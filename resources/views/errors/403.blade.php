@extends('errors::minimal')

@section('title', __('messages.error_forbidden'))
@section('code', '403')
@section('message', $exception->getMessage() ?: __('messages.error_forbidden'))
