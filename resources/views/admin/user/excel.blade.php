<table>
    <thead>
    <tr>
        <th width="20">No</th>
        <th width="20">Nama</th>
        <th width="20">Email</th>
        <th width="20">Jabatan</th>
    </tr>
    </thead>
    <tbody>
        @foreach ($user as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->nama }}</td>
                <td>{{ $item->email }}</td>
                <td>{{ $item->jabatan }}</td>
            </tr>
        @endforeach
    </tbody>
</table>