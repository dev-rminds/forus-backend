@extends('emails.base')

@section('button_text', mail_trans('provider_applied.button_text'))
@section('link', $sponsor_dashboard_link)
@section('title', mail_trans('provider_applied.title', [
    'provider_name' => $provider_name,
    'fund_name' => $fund_name
]))

@section('html')
    {{ mail_trans('dear_sponsor', ['sponsor_name' => $sponsor_name]) }},
    <br/>
    <br/>
    {{ mail_trans('provider_applied.new_applicant', ['fund_name' => $fund_name]) }}
    <br/>
    <br/>
    {{ mail_trans('provider_applied.check_if_valid', ['provider_name' => $provider_name]) }}
    <br/>
    {!! mail_trans('provider_applied.apply_on_dashboard', ['link' => $sponsor_dashboard_link]) !!}
@endsection
