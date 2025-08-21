@extends('layouts.app')

@section('content')

<div class="max-w-4xl mx-auto py-10">

    <h2 class="text-2xl font-semibold mb-6 text-center">Convert Word → PDF</h2>
    <form action="{{ route('word.to.pdf.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6 text-center">
        @csrf
        <input type="file" name="file" id="inputFile" accept=".doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" required class="border p-3 rounded w-full max-w-md mx-auto">
        <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-full hover:bg-blue-700">Convert</button>
    </form>
</div>
@endsection
