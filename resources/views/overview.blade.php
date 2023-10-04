@extends('templates.dashboard')

@section('content')
  {{-- <div class="row">
    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
      <div class="card">
        <div class="card-header p-3 pt-2">
          <div
            class="icon icon-lg icon-shape bg-gradient-primary shadow-primary text-center border-radius-xl mt-n4 position-absolute">
            <i class="material-icons opacity-10">person</i>
          </div>
          <div class="text-end pt-1">
            <p class="text-sm mb-0 text-capitalize">Today's Users</p>
            <h4 class="mb-0">2,300</h4>
          </div>
        </div>
        <hr class="dark horizontal my-0">
        <div class="card-footer p-3">
          <p class="mb-0"><span class="text-success text-sm font-weight-bolder">+3% </span>than
            last month</p>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
      <div class="card">
        <div class="card-header p-3 pt-2">
          <div
            class="icon icon-lg icon-shape bg-gradient-dark shadow-dark text-center border-radius-xl mt-n4 position-absolute">
            <!-- <i class="material-icons opacity-10">weekend</i> -->
            <i class="fa-regular fa-pen-to-square opacity-10"></i>
          </div>
          <div class="text-end pt-1">
            <p class="text-sm mb-0 text-capitalize">Today's Insert</p>
            <h4 class="mb-0">$53k</h4>
          </div>
        </div>
        <hr class="dark horizontal my-0">
        <div class="card-footer p-3">
          <p class="mb-0"><span class="text-success text-sm font-weight-bolder">+55% </span>than
            last week</p>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
      <div class="card">
        <div class="card-header p-3 pt-2">
          <div
            class="icon icon-lg icon-shape bg-gradient-success shadow-success text-center border-radius-xl mt-n4 position-absolute">
            <i class="fa-regular fa-paste opacity-10"></i>
          </div>
          <div class="text-end pt-1">
            <p class="text-sm mb-0 text-capitalize">Today's Update</p>
            <h4 class="mb-0">3,462</h4>
          </div>
        </div>
        <hr class="dark horizontal my-0">
        <div class="card-footer p-3">
          <p class="mb-0"><span class="text-danger text-sm font-weight-bolder">-2%</span> than
            yesterday</p>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-sm-6">
      <div class="card">
        <div class="card-header p-3 pt-2">
          <div
            class="icon icon-lg icon-shape bg-gradient-info shadow-info text-center border-radius-xl mt-n4 position-absolute">
            <i class="fa-regular fa-trash-can opacity-10"></i>
          </div>
          <div class="text-end pt-1">
            <p class="text-sm mb-0 text-capitalize">Today's Delete</p>
            <h4 class="mb-0">$103,430</h4>
          </div>
        </div>
        <hr class="dark horizontal my-0">
        <div class="card-footer p-3">
          <p class="mb-0"><span class="text-success text-sm font-weight-bolder">+5% </span>than
            yesterday</p>
        </div>
      </div>
    </div>
  </div> --}}
  <div class="row mt-4">
    @foreach ($tables as $tbl)
      <div class="col-12 col-md-6 monitor" data-src="{{ $tbl->src_table }}" data-dest="{{ $tbl->dest_table }}">
        <div class="card card-body">
          <h2>{{ $tbl->src_table }} Table</h2>
          @if ($tbl->n_consumers > 0)
            <div class="running text-success"><i class="fas fa-play"></i> Running </div>
          @else
            <div class="running text-danger"><i class="fas fa-xmark"></i> Stopped </div>
          @endif
          <hr class="my-2">
          <strong>Overview</strong>
          <table>
            <tbody>
              <tr>
                <td>Destination Table</td>
                <td>{{ $tbl->dest_table }}</td>
              </tr>
              <tr>
                <td>Topic</td>
                <td>replication</td>
              </tr>
              <tr>
                <td>Consumers</td>
                <td class="n_consumers">{{ $tbl->n_consumers }}</td>
              </tr>
              <tr>
                <td>Rows Inserted</td>
                <td class="insert">{{ $tbl->n_insert ?? 0 }}</td>
              </tr>
              <tr>
                <td>Rows Updated</td>
                <td class="update">{{ $tbl->n_update ?? 0 }}</td>
              </tr>
              <tr>
                <td>Rows Deleted</td>
                <td class="delete">{{ $tbl->n_delete ?? 0 }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    @endforeach
  </div>
  <script>
    function set_running(src, dest, running) {
      $(`div[data-src="${src}"][data-dest="${dest}"] .running`)
        .attr("class", "running")
        .addClass(running ? "text-success" : "text-danger")
        .text(` ${running?"Running":"Stopped"}`)
        .prepend(
          $("<i>")
          .attr("class", `fas fa-${running ?"play":"xmark"}`)
        )
    }
    const sio = io("http://192.168.21.7:5000"),
      consumers = new Map()
    sio.on("connect", () => {
      console.log("connected")
      $(".monitor").each(function() {
        const src = $(this).attr("data-src")
        const dest = $(this).attr("data-dest")
        sio.emit("handshake", {
          type: "monitor",
          src,
          dest,
        })
      })
    })
    sio.on("handshake_success", ({
      n_consumers,
      src,
      dest
    }) => {
      const data = $(`div[data-src="${src}"][data-dest="${dest}"] .n_consumers`).text(n_consumers)
      set_running(src, dest, n_consumers > 0)
    })
    sio.on("update_crud", ({
      type,
      src,
      dest,
      count
    }) => {
      const data = $(`div[data-src="${src}"][data-dest="${dest}"] .${type}`).text(count)
    })
    sio.on("connect_error", () => {
      console.log("disconnected")
      $(".monitor").each(function() {
        const src = $(this).attr("data-src")
        const dest = $(this).attr("data-dest")
        const data = $(this).find(".n_consumers").text(0)
        set_running(src, dest, false)
      })
    })
  </script>
@endsection
