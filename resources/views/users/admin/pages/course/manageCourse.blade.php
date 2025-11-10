{{-- resources/views/dashboard.blade.php --}}

@extends('users.admin.components.structure')

@section('title', 'Manage Course')
@section('header', 'Dashboard')

@section('content')
<div class="container">
    @include('modules.course.manageCourse')
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
