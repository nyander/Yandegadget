@extends('layouts.app')

@section('content')

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Dashboard</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    You are logged in as:  {{Auth::user()->name}}
                </div>
                @can('supplier-role')
                    @if ($checker)
                        <a href="{{ route('suppliers.create') }}"> <button class="btn btn-success" type="submit">Add Details</button> </a>
                    @else 
                    <a href="{{route('suppliers.edit', $suppliers)}}"> <button class="btn btn-success" type="submit">Manage Details</button> </a>
                    @endif
                @endcan
                
            </div>
        </div>
        
    </div>

@endsection
