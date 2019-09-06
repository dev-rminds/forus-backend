@extends('emails.base')
@section('title', mail_trans('fund_expires.title'))
@section('html')
{{ mail_trans('dear_user_of_fund', ['fund_name' => $fund_name])}}
<br/>
    {{ mail_trans('fund_expires.on_date_have_recieved', [
        'start_date_fund' => $start_date_fund,
        'end_date_fund' => $start_date_fund,
        'sponsor_name' => $sponsor_name,
        'fund_name' => $fund_name
    ]) }}
    <br/>
    {{ mail_trans('fund_expires.voucher_due_to', [
        'fund_name' => $fund_name,
        'end_date_fund' => $end_date_fund,
        'not_legit_anymore_date' => Carbon\Carbon::createFromFormat('l, d F Y', $end_date_fund)->addDay()->format('l, d F Y')
    ]) }}
    <br/>
    {!! mail_trans('fund_expires.see_budget_and_transactions', ['link' => $shop_implementation_url]) !!}
    {{ mail_trans('fund_expires.next_log_in') }}
    <br/>
    {{ mail_trans('fund_expires.get_in_contact') }}
    <br/>
    {{ $phonenumber_sponsor }}
    {{ $emailaddress_sponsor }}
    <br/>
    {{ mail_trans('hopefully_informed_enough') }}
    <br/>
    {{ mail_trans('greets') }}
    <br />
    {{ mail_trans('team_fund', ['fund_name' => $fund_name]) }}
    {{ $sponsor_name }}
@endsection
