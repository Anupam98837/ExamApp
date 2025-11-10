{{-- resources/views/dashboard.blade.php --}}

@extends('users.student.components.structure')

@section('title', 'Suggested Exam')
@section('header', 'Dashboard')

@section('content')
<div class="container mt-5">
    <div class="section-header text-center mb-5">
        <h2 class="cta-title fade-in-up">Available Competitive Exams</h2>
        <p class="cta-subtitle fade-in-up">Choose from our comprehensive collection of mock tests designed by experts</p>
    </div>
    @include('modules.exam.suggestedExams')
</div>
@endsection

@section('scripts')
<script>
  // On DOM ready, verify token; if missing, redirect home
  document.addEventListener('DOMContentLoaded', function() {
    if (!sessionStorage.getItem('token')) {
      window.location.href = '/';
    }
  });
  
</script>
@endsection
