<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Data User</title>
<style>
    body {
        font-family: Arial, sans-serif;
        font-size: 12px;
        margin: 25px;
    }

    h1 {
        text-align: center;
        margin-bottom: 10px;
    }

    hr {
        width: 80%;
        margin: 5px auto 20px;
        border: 1px solid #000;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed; /* penting biar kolom rata */
    }

    th, td {
        border: 1px solid #ddd;
        padding: 6px 8px;
        text-align: left;
        word-wrap: break-word;
    }

    thead {
        background-color: #4B3BFF;
        color: white;
    }

    th:nth-child(1) { width: 8%; }   /* No */
    th:nth-child(2) { width: 27%; }  /* Nama */
    th:nth-child(3) { width: 40%; }  /* Email */
    th:nth-child(4) { width: 25%; }  /* Jabatan */

    tbody tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    tbody tr:hover {
        background-color: #f1f1ff;
    }
</style>
</head>
<body>

<h1>Data User</h1>
<hr>

<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Nama</th>
            <th>Email</th>
            <th>Jabatan</th>
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

</body>
</html>
