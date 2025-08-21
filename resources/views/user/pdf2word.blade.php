@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-10">

    <h2 class="text-2xl font-semibold mb-6 text-center">Convert PDF → Word</h2>

    <form id="form" method="POST" action="{{ route('pdf2word.run') }}" enctype="multipart/form-data" class="space-y-6 text-center">
        @csrf
        <input type="file" name="pdfs[]" multiple accept=".pdf" required class="border p-3 rounded w-full max-w-md mx-auto">
        <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-full hover:bg-blue-700">
            Convert & Download
        </button>
    </form>

</div>
@endsection
