@if(session('success'))<div class="alert success" role="status"><x-icon name="check"/><span>{{ session('success') }}</span></div>@endif
@if($errors->any())<div class="alert error" role="alert"><x-icon name="info"/><div><strong>Revisa los siguientes datos</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>@endif
