@extends('layouts.app')

@section('title', 'Tools Data Anggaran')
@section('page-title', 'Tools Data Anggaran')

@section('content')

<div class="container">
    <!-- Section untuk menampilkan pesan -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-lg border-0" data-aos="fade-up" data-aos-delay="200">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-table"></i> Data Anggaran</h5>
                    <div class="mb-3">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                            <i class="bi bi-cloud-upload-fill"></i> Upload Data Anggaran
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table id="dataAnggaranTable" class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tipe Data</th>
                                    <th>Jumlah Baris</th>
                                    <th>Tanggal Upload</th>
                                    <th>Jam Upload</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $no = 1;
                                @endphp
                                @foreach ($dataAnggarans as $data)
                                    <tr>
                                        <td>{{ $no++ }}</td>
                                        <td>{{ optional($tahapans->find($data->tahapan_id))->name ?? 'Unknown' }}</td>
                                        <td>{{ $data->jumlah }}</td>
                                        <td>{{ $data->tanggal_upload }}</td>
                                        <td>{{ $data->jam_upload }}</td>
                                        <td>
                                            <button class="btn btn-danger btn-sm" onclick="confirmDelete('{{ $data->tahapan_id }}', '{{ $data->tanggal_upload }}', '{{ $data->jam_upload }}')">Delete</button>
                                            <form id="delete-form-{{ $data->tahapan_id }}-{{ $data->tanggal_upload }}-{{ $data->jam_upload }}" action="{{ route('data-anggaran.destroy', [$data->tahapan_id, $data->tanggal_upload, $data->jam_upload]) }}" method="POST" style="display:none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadModalLabel">Upload Data Anggaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('data-anggaran.upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="tahapan_id" class="form-label">Tipe Data</label>
                        <select name="tahapan_id" id="tahapan_id" class="form-select" required>
                            @foreach ($tahapans as $tahapan)
                                <option value="{{ $tahapan->id }}">{{ $tahapan->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="tanggal_upload" class="form-label">Tanggal Upload</label>
                        <input type="datetime-local" name="tanggal_upload" id="tanggal_upload" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="file" class="form-label">File Excel</label>
                        <input type="file" name="file" id="file" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmDelete(tahapan_id, tanggal_upload, jam_upload) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + tahapan_id + '-' + tanggal_upload + '-' + jam_upload).submit();
            }
        })
    }
</script>

@endsection
