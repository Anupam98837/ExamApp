{{-- resources/views/dashboard.blade.php --}}

@extends('users.student.components.structure')

@section('title', 'View Result')
@section('header', 'Dashboard')

@section('content')
    @include('modules.result.viewResult')
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
