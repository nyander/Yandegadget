@if (count($errors) > 0)
      <div class="alert alert-danger">
        <strong>Sorry !</strong> There were some problems with your input.<br><br>
        <ul>
          @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
      @endif


@if(session('success'))
        <div class="alert alert-success" role="alert" class="my-5">
            {{session('success')}}
        </div>    
@endif

@if(session('error'))
        <div class="alert alert-danger" role="alert" class="my-5">
            {{session('error')}}
        </div>    
@endif

@if(session('warning'))
        <div class="alert alert-warning" role="alert" class="my-5">
            {{session('warning')}}
        </div>    
@endif