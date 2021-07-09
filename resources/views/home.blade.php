@extends('layouts.home')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-12">
                    <h1 class="m-0">Массовое редактирование полей</h1>
                </div>
            </div>
        </div>
    </div>
    <div class="content">
        <div class="container-fluid">
            <div class="row mt-5">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            @if(!$is_running)
                                @if(session('success'))
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="alert alert-success mb-5">
                                                {{session('success')}}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                              @if(session('error'))
                                  <div class="row">
                                      <div class="col-12">
                                          <div class="alert alert-danger mb-5">
                                              {{session('error')}}
                                          </div>
                                      </div>
                                  </div>
                              @endif


                             <form action="{{route('processHandler')}}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="entity_id">Выберите тип сущности</label>
                                            <select name="entity_id" id="entity_id" class="form-control @error('entity_id') is-invalid @enderror">
                                                <option value="">Выбрать</option>
                                                @foreach(\App\Models\Entity::pluck('title','id') as $id=>$title)
                                                    <option
                                                        value="{{$id}}" {{old('entity_id') === $id ? 'selected':''}}>{{$title}}</option>
                                                @endforeach
                                            </select>
                                            @error('entity_id')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="file">Файл</label>
                                            <div class="custom-file">
                                                <input type="file" name="file" class="custom-file-input @error('file') is-invalid @enderror" id="file">
                                                <label class="custom-file-label" for="file">Выбрать файл</label>
                                            </div>
                                            @error('file')
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="startBtn">Запуск процесса</label>
                                            <button
                                                type="submit"
                                                id="startBtn"
                                                class="btn btn-success btn-block"

                                            >
                                                Запустить процесс
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                             <div class="row mt-5">
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-body history-card">
                                                <div id="titleContainer">
                                                    <h4 id="historyTitle" class="d-inline">История последнего процесса</h4>

                                                </div>
                                                <hr>
                                                @if($lastProcess)
                                                <p>Время запуска: <span id="process_start"> {{$lastProcess->process_start}}</span></p>
                                                <p>Время завершения: <span id="process_end"> {{$lastProcess->process_end}}</span></p>
                                                <p>Тип сущности: <span id="entity_title"> {{$lastProcess->entity->title}}</span></p>
                                                <p>Строк в файле: <span id="lines_count"> {{$lastProcess->lines_count}}</span></p>
                                                <p>Успешно обработано: <span id="lines_success"> {{$lastProcess->lines_success}}</span></p>
                                                <p>Некритичных ошибок: <span id="lines_error"> {{$lastProcess->lines_error}}</span></p>
                                                <p>Ссылка на лог ошибок: <a href="{{route('getLog')}}">лог ошибок</a></p>
                                                @else
                                                <p>Процессы не найдены</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                @if(session('success'))
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="alert alert-success mb-5">
                                                {{session('success')}}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <div class="row mt-5">
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-body history-card">
                                                <div id="titleContainer">
                                                    <h4 id="historyTitle" class="d-inline">Идет выполнение процесса...</h4>
                                                    <button
                                                        onclick="location.reload()"
                                                        class="btn btn-sm btn-success d-inline ml-3 mr-3">
                                                        <i class="fa fa-sync mr-2"></i>
                                                        обновить
                                                    </button>
                                                    <form action="{{route('processTerminate')}}" method="POST" class="d-inline">
                                                        @csrf
                                                    <button
                                                        type="submit"
                                                        onclick="confirm('Вы уверены, что хотите прервать процесс?')"
                                                        id="terminateProcessBtn" class="btn btn-sm btn-danger d-inline">
                                                        <i class="fa fa-minus-circle mr-2"></i>
                                                        прервать процесс
                                                    </button>
                                                    </form>
                                                </div>
                                                <hr>
                                                <p>Время запуска: <span id="process_start"> {{$lastProcess->process_start}}</span></p>
                                                <p>Время завершения: <span id="process_end"> {{$lastProcess->process_end}}</span></p>
                                                <p>Тип сущности: <span id="entity_title"> {{$lastProcess->entity->title}}</span></p>
                                                <p>Строк в файле: <span id="lines_count"> {{$lastProcess->lines_count}}</span></p>
                                                <p>Успешно обработано: <span id="lines_success"> {{$lastProcess->lines_success}}</span></p>
                                                <p>Некритичных ошибок: <span id="lines_error"> {{$lastProcess->lines_error}}</span></p>
                                                <p>Ссылка на лог ошибок: <a href="{{route('getLog')}}" >лог ошибок</a></p>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif


                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>




@endsection
