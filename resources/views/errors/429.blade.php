@extends('errors::minimal')

@section('title', __('messages.error_too_many_requests'))
@section('code', '429')
@section('message', __('messages.error_too_many_requests'))
