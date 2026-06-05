<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\X1RoomInstance;
use App\Models\X1Result;
use App\Events\X1RoomClosed;

class X1AdminController extends Controller
{
    public function index(Request $request)
    {
        $query = X1RoomInstance::latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('id', $search);
            });
        }

        $rooms = $query->paginate(25);
        
        return view('admin.x1.index', compact('rooms'));
    }

    public function show(X1RoomInstance $room)
    {
        $room->load([
            'host',
            'participants.user',
            'participants.competitor',
            'participants.competitorGroup.members',
            'competitor',
            'competitorGroup.members',
            'modalidade',
            'result',
        ]);
        
        // Buscar resultado X1 para determinar vencedor/perdedor
        $x1Result = X1Result::where('x1_room_id', $room->id)->first();
        
        return view('admin.x1.show', compact('room', 'x1Result'));
    }

    public function close(Request $request, X1RoomInstance $room)
    {
        $room->status = 'closed';
        $room->closed_at = now();
        $room->save();

        event(new X1RoomClosed($room));

        return redirect()->route('admin.x1.show', $room->id)->with('success', 'Sala encerrada e processamento enfileirado.');
    }

    public function participants(X1RoomInstance $room)
    {
        $participants = $room->participants()->with('user')->get();
        return view('admin.x1.participants', compact('room','participants'));
    }

    /**
     * Marcar prêmio como pago (PIX realizado)
     */
    public function markPrizePaid(Request $request, X1RoomInstance $room)
    {
        $x1Result = X1Result::where('x1_room_id', $room->id)->first();

        if (!$x1Result) {
            return redirect()->back()->with('error', 'Resultado não encontrado para esta sala.');
        }

        if ($x1Result->prize_paid_at) {
            return redirect()->back()->with('warning', 'Este prêmio já foi marcado como pago.');
        }

        $x1Result->prize_paid_at = now();
        $x1Result->prize_paid_by = auth()->guard('admin')->id();
        $x1Result->save();

        $notify[] = ['success', 'Prêmio marcado como pago com sucesso!'];
        return redirect()->back()->withNotify($notify);
    }

    /**
     * Excluir sala X1 permanentemente
     */
    public function destroy(X1RoomInstance $room)
    {
        $roomId = $room->id;
        $roomName = $room->name;

        // Apagar registros relacionados
        X1Result::where('x1_room_id', $roomId)->delete();
        $room->participants()->delete();
        $room->delete();

        return redirect()->route('admin.x1.index')->with('success', "Sala #{$roomId} ({$roomName}) excluída permanentemente.");
    }
}
