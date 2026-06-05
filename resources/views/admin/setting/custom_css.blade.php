@extends('admin.layouts.app')
@section('panel')
    <div class="row mb-none-30">
      <div class="col-md-12 mb-30">
            <div class="card bl--5 border--primary">
                <div class="card-body">
                    <p class="text--primary">Nesta página, você pode adicionar/atualizar o CSS da interface do usuário. Alterar o conteúdo aqui requer conhecimento de programação.</p>
                    <p class="text--warning">Não altere/edite/adicione nada sem o conhecimento adequado. O site pode apresentar comportamentos inesperados devido a qualquer erro cometido.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h6>Escrever CSS personalizado</h6>
                </div>
                <form method="post">
                    @csrf
                    <div class="card-body">
                        <div class="form-group custom-css">
                            <textarea class="form-control customCss" rows="10" name="css">{{ $fileContent }}</textarea>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn--primary w-100 h-45">Enviar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('style')
<style>
    .CodeMirror{
        border-top: 1px solid #eee;
        border-bottom: 1px solid #eee;
        line-height: 1.3;
        height: 500px;
    }
    .CodeMirror-linenumbers{
      padding: 0 8px;
    }
    .custom-css p, .custom-css li, .custom-css span{
      color: white;
    }
  </style>
@endpush
@push('style-lib')
    <link rel="stylesheet" href="{{asset('assets/admin/css/codemirror.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/admin/css/monokai.min.css')}}">
@endpush
@push('script-lib')
    <script src="{{asset('assets/admin/js/codemirror.min.js')}}"></script>
    <script src="{{asset('assets/admin/js/css.min.js')}}"></script>
    <script src="{{asset('assets/admin/js/sublime.min.js')}}"></script>
@endpush
@push('script')
<script>
    "use strict";
    var editor = CodeMirror.fromTextArea(document.getElementsByClassName("customCss")[0], {
      lineNumbers: true,
      mode: "text/css",
      theme: "monokai",
      keyMap: "sublime",
      autoCloseBrackets: true,
      matchBrackets: true,
      showCursorWhenSelecting: true,
      matchBrackets: true
    });
</script>
@endpush
