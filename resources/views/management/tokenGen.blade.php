@extends('layouts.app')

@section('custom_link')
    {{--<link rel="stylesheet" href={{asset('')}} >--}}
    {{--<script src={{asset()}}></script>--}}
@endsection

@section('content')

    {!! Form::open(array('url'=>'requestToken', 'method'=>'post', 'class'=>'form-horizontal')) !!}
    <div class="form-group">
        {!! Form::label('api_token', "API_Token:&nbsp;&nbsp;", array('class'=>'control-label col-sm-2')) !!}
        <div class="col-sm-8">
            {!! Form::text('api_token', isset($api_token)?$api_token:'', array('class'=>'form-control', 'id'=>'api_token', 'placeholder'=> 'API token', isset($api_token)?'':'readonly')) !!}
        </div>
    </div>
    {!! Form::token() !!}
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            {!! Form::submit('Apply Token', array('class'=>isset($api_token)?'btn-default':'btn-primary' . ' btn', isset($api_token)?'disabled':'')) !!}
        </div>
    </div>

    {!! Form::close() !!}
    <br />

@endsection
