@extends('admin.layouts.app')
@section('panel')
    <div class="row mb-none-30">
        <div class="col-md-12 mb-30">
            <div class="card bl--5 border--primary">
                <div class="card-body">
                    <p class="text--primary">Se o logo e o favicon não forem alterados após a atualização nesta página, por favor <a href="{{ route('admin.system.optimize.clear') }}" class="text--info text-decoration-underline">limpe o cache</a> do seu navegador. Como mantemos o mesmo nome de arquivo após a atualização, o cache pode exibir a imagem antiga. Normalmente funciona após limpar o cache, mas se você ainda ver o logo ou favicon antigo, isso pode ser causado por cache em nível de servidor ou de rede. Limpe-os também.</p>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row justify-content-center">
                            <div class="form-group col-md-8 col-sm-6">
                                <label> Logo</label>
                                <x-image-uploader name="logo" :imagePath="siteLogo() . '?' . time()" :size="false" class="w-100" id="uploadLogo" :required="false" />
                            </div>
                            <div class="form-group col-md-4 col-sm-6">
                                <label> Favicon</label>
                                <x-image-uploader name="favicon" :imagePath="siteFavicon() . '?' . time()" :size="false" class="w-100" id="uploadFavicon" :required="false" />
                            </div>
                        </div>
                        <button type="submit" class="btn btn--primary w-100 h-45">Enviar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
