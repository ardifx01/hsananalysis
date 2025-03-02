hsananalysis2/resources/views/tahapan/edit.blade.php

@extends('layouts.app')

@section('content')
    <h1>Edit Tahapan</h1>
    <form action="{{ route('tahapan.update', $tahapan->id) }}" method="POST">
        @csrf
        @method('PUT')
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" value="{{ $tahapan->name }}" required>
        <label for="description">Description:</label>
        <textarea id="description" name="description">{{ $tahapan->description }}</textarea>
        <button type="submit">Update</button>
    </form>
@endsection