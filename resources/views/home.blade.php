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

                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="entity_id">Выберите тип сущности</label>
                                            <select name="entity_id" id="entity_id" class="form-control">
                                                <option value="">Выбрать</option>
                                                @foreach(\App\Models\Entity::pluck('title','id') as $id=>$title)
                                                    <option
                                                        value="{{$id}}" {{old('entity_id') === $id ? 'selected':''}}>{{$title}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="file">Файл(CSV)</label>
                                            <div class="custom-file">
                                                <input type="file" name="file" class="custom-file-input" id="file">
                                                <label class="custom-file-label" for="file">Выбрать файл</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="startBtn">Запуск процесса</label>
                                            <button
                                                type="button"
                                                id="startBtn"
                                                class="btn btn-success btn-block"

                                            >
                                                Запустить процесс
                                            </button>
                                        </div>
                                    </div>
                                </div>


                            <div class="row mt-5">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body history-card">
                                            <div id="titleContainer">
                                                <h4 id="historyTitle" class="d-inline">История последнего процесса</h4>

                                            </div>
                                            <hr>
                                            <p>Время запуска: <span id="process_start"> {{$lastProcess->process_start}}</span></p>
                                            <p>Время завершения: <span id="process_end"> {{$lastProcess->process_end}}</span></p>
                                            <p>Тип сущности: <span id="entity_title"> {{$lastProcess->entity->title}}</span></p>
                                            <p>Строк в файле: <span id="lines_count"> {{$lastProcess->lines_count}}</span></p>
                                            <p>Успешно обработано: <span id="lines_success"> {{$lastProcess->lines_success}}</span></p>
                                            <p>Некритичных ошибок: <span id="lines_error"> {{$lastProcess->lines_error}}</span></p>
                                            <p>Ссылка на лог ошибок: <a href="https://yandex.ru" target="_blank">error_log_link_here</a></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>




@endsection
