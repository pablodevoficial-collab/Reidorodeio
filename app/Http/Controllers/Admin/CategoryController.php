<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Lib\OddsApi\OddsApi;
use App\Models\Rodeio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RodeioController extends Controller {
    public function index() {
        $pageTitle  = 'Todos os Rodeios';
    $rodeios = Rodeio::with('modalidades')->orderBy('id', 'desc')->paginate(getPaginate());
    return view('admin.category', compact('pageTitle', 'rodeios'));
    }

    public function store(Request $request, $id = 0) {
        $request->validate([
            'name'  => 'required|max:40|unique:rodeios,name,' . $id,
            'start' => 'required|date',
            'end'   => 'required|date|after_or_equal:start',
            'logo'  => $id ? 'nullable|image|mimes:png|max:2048' : 'required|image|mimes:png|max:2048',
        ], [
            'logo.image' => 'O logo deve ser uma imagem PNG.',
            'logo.required' => 'O campo logo é obrigatório.',
            'logo.mimes' => 'O logo deve ser um arquivo PNG.',
            'logo.max' => 'O logo não pode ter mais que 2MB.',
        ]);
        $id = intval($id);
        \Log::info('Validação Rodeio', ['id' => $id, 'request' => $request->all()]);

        if ($id) {
            $rodeio     = Rodeio::findOrFail($id);
            $notification = 'Rodeio atualizado com sucesso';
        } else {
            $rodeio     = new Rodeio();
            $notification = 'Rodeio adicionado com sucesso';
        }

        try {
            $rodeio->name  = $request->name;
            $rodeio->start = $request->start;
            $rodeio->end   = $request->end;
            if ($request->hasFile('logo')) {
                $logo      = $request->file('logo');
                $logoName  = uniqid() . '.png';
                $logo->move(public_path('assets/images/logo_rodeio'), $logoName);
                $rodeio->logo = $logoName;
            } elseif ($id && $rodeio->logo) {
                // Mantém logo anterior
            }
            $rodeio->save();
        } catch (\Throwable $e) {
            \Log::channel('single')->error('[RODEIO STORE] ' . $e->getMessage() . ' | File: ' . $e->getFile() . ' | Line: ' . $e->getLine());
            file_put_contents(storage_path('logs/rodeio_error.log'), date('Y-m-d H:i:s') . ' - ' . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n", FILE_APPEND);
            return back()->withErrors(['erro' => 'Erro ao salvar rodeio. Verifique os logs.']);
        }

        $notify[] = ['success', $notification];
        return back()->withNotify($notify);
    }


    public function saveFetchedCategories(Request $request) {
        $request->validate([
            'rodeios' => 'required|array|min:1',
            'rodeios.*' => 'required|string'
        ], [
            'rodeios.required' => 'Selecione pelo menos um rodeio para salvar',
        ]);

        foreach ($request->rodeios as $rodeioName) {
            \App\Models\Rodeio::create([
                'name' => $rodeioName,
                // Adicione outros campos se necessário, como start, end, logo
            ]);
        }

        $notify[] = ['success', 'Rodeios salvos com sucesso'];
        return back()->withNotify($notify);
    }
}
