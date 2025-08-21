@extends('layouts.app')

@section('content')

<div class="max-w-4xl mx-auto py-10">

    <h2 class="text-2xl font-semibold mb-6 text-center">Power Point → PDF</h2>
    <form action="{{ route('ppt2pdf') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div>
            <label for="file">Upload PowerPoint File (.ppt or .pptx):</label>
            <input type="file" name="file" accept=".ppt,.pptx" required>
        </div>
        <button type="submit" class="mt-4 bg-blue-600 text-white px-4 py-2 rounded">Convert to PDF</button>
    </form>

    @if(session('error'))
    <div class="text-red-600 mt-4">{{ session('error') }}</div>
    @endif
</div>
@endsection
