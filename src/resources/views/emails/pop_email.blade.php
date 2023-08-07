@extends('emails.pop_template')

@section('emails.main')
<div style="margin:0;padding:0;font-family:&quot;Helvetica Neue&quot;,&quot;Helvetica&quot;,Helvetica,Arial,sans-serif;margin-top:1em">
	<p> Name : {{$data['name']}}</p>
	<p> {{ucfirst($data['type'])}} : {{(($data['type']=='whatsapp') ? ('+'.$data['country_code'].' - '.$data['phone_number']) :($data['type']=='skype' ?$data['skype']:$data['email'])) }}</p>
	<p> Message : {{$data['message']}}</p>
</div>
@stop