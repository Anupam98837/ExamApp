{{-- resources/views/exam/viewAllExam.blade.php --}}
@extends('landingPage.layout.structure')
 
@section('title', 'Available Exams – Horizon Allienz')
 
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/common/welcome.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/exam/viewAllExam.css') }}">
@endpush
 
@section('content')
@php  $assetBase = asset('');  @endphp
<div>
    @include('modules.exam.buyExam')
</div>
@endsection
 
@push('scripts')
@endpush