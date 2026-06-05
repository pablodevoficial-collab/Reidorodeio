<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdsController extends Controller
{
    public function banners()
    {
        $pageTitle = 'Anúncios • Banners';
        $banners = Banner::orderBy('id', 'desc')->get();
        $emptyMessage = 'Nenhum banner cadastrado.';

        return view('admin.ads.banners', compact('pageTitle', 'banners', 'emptyMessage'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required',
            'link' => 'required|url',
            'imagem_web' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:10240',
            'imagem_mobile' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:10240',
            'posicao' => 'required',
            'status' => 'required|in:ativo,inativo',
        ]);

        $banner = new Banner();
        $banner->title = $request->titulo;
        $banner->link = $request->link;
        $banner->position = $request->posicao;
        $banner->start_date = $request->inicio;
        $banner->end_date = $request->fim;
        $banner->status = $request->status;

        // Upload imagem web
        if ($request->hasFile('imagem_web')) {
            $path = $request->file('imagem_web')->store('banners', 'public');
            $banner->image_web = basename($path);
            $banner->image = basename($path); // Manter compatibilidade
        }

        // Upload imagem mobile
        if ($request->hasFile('imagem_mobile')) {
            $path = $request->file('imagem_mobile')->store('banners', 'public');
            $banner->image_mobile = basename($path);
        }

        $banner->save();

        $notify[] = ['success', 'Banner cadastrado com sucesso!'];
        return back()->withNotify($notify);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'titulo' => 'required',
            'link' => 'required|url',
            'imagem_web' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:10240',
            'imagem_mobile' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:10240',
            'posicao' => 'required',
            'status' => 'required|in:ativo,inativo',
        ]);

        $banner = Banner::findOrFail($id);
        $banner->title = $request->titulo;
        $banner->link = $request->link;
        $banner->position = $request->posicao;
        $banner->start_date = $request->inicio;
        $banner->end_date = $request->fim;
        $banner->status = $request->status;

        // Upload nova imagem web (se fornecida)
        if ($request->hasFile('imagem_web')) {
            // Deletar imagem anterior
            if($banner->image_web) {
                Storage::disk('public')->delete('banners/' . $banner->image_web);
            }
            
            $path = $request->file('imagem_web')->store('banners', 'public');
            $banner->image_web = basename($path);
            $banner->image = basename($path); // Manter compatibilidade
        }

        // Upload nova imagem mobile (se fornecida)
        if ($request->hasFile('imagem_mobile')) {
            // Deletar imagem anterior
            if($banner->image_mobile) {
                Storage::disk('public')->delete('banners/' . $banner->image_mobile);
            }
            
            $path = $request->file('imagem_mobile')->store('banners', 'public');
            $banner->image_mobile = basename($path);
        }

        $banner->save();

        $notify[] = ['success', 'Banner atualizado com sucesso!'];
        return back()->withNotify($notify);
    }

    public function delete($id)
    {
        $banner = Banner::findOrFail($id);
        
        // Deletar ambas as imagens
        if($banner->image_web) {
            Storage::disk('public')->delete('banners/' . $banner->image_web);
        }
        if($banner->image_mobile) {
            Storage::disk('public')->delete('banners/' . $banner->image_mobile);
        }
        // Deletar ambas as imagens
        if($banner->image_web) {
            Storage::disk('public')->delete('banners/' . $banner->image_web);
        }
        if($banner->image_mobile) {
            Storage::disk('public')->delete('banners/' . $banner->image_mobile);
        }
        if($banner->image) {
            Storage::disk('public')->delete($banner->image);
        }
        
        $banner->delete();
        $notify[] = ['success', 'Banner removido com sucesso!'];
        return back()->withNotify($notify);
    }
}
