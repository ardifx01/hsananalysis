@extends('layouts.app')

@section('content')
    <h1>{{ $tahapan->name }}</h1>
    <p>{{ $tahapan->description }}</p>
    <a href="{{ route('tahapan.edit', $tahapan->id) }}">Edit</a>
    <form action="{{ route('tahapan.destroy', $tahapan->id) }}" method="POST">
        @csrf
        @method('DELETE')
        <button type="submit">Delete</button>
    </form>
@endsection