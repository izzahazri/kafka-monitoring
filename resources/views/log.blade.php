@extends('templates.dashboard')

@section('content')
  <style>
    #log td {
      color: white !important;
    }

    .error {
      background-color: rgb(250, 102, 102) !important;
    }

    .info {
      background-color: rgb(34, 27, 105) !important;
    }

    .success {
      background-color: rgb(22, 119, 38) !important;
    }

    .warn {
      background-color: rgb(131, 140, 27) !important;
    }

    #log td.dataTables_empty {
      color: black !important;
    }
  </style>
  <table id="log" class="table table-striped">
    <thead>
      <tr>
        <th>#</th>
        <th>Level</th>
        <th>Details</th>
        <th>Timestamp</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($logs as $i => $log)
        <tr class="{{ $log->log_type }}">
          <td>{{ $i + 1 }}</td>
          <td>{{ strtoupper($log->log_type) }}</td>
          <td>{{ $log->log_details }}</td>
          <td>{{ $log->insert_date }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
  <script>
    const table = $('#log').DataTable()
    const sio = io("http://192.168.21.7:5000")
    sio.on("connect", () => {
      console.log("connected")
    })
    sio.on("update_error", ({
      type,
      message,
      timestamp
    }) => {
      const index = $(`#log tbody tr`).length + 1
      table.row.add(
          $('<tr>')
          .addClass(type)
          .append($("<td>").text(index))
          .append($("<td>").text(type.toUpperCase()))
          .append($("<td>").text(message))
          .append($("<td>").text(timestamp))
        )
        .draw()
    })
  </script>
@endsection
