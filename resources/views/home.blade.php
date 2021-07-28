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
                                @if(request('success'))

                                    <div class="row">
                                        <div class="col-12">
                                            <div class="alert alert-success mb-5">
                                                {{request('success')}}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                @if(request('message'))
                                  <div class="row">
                                      <div class="col-12">
                                          <div class="alert alert-danger mb-5">
                                              {!! request('message') !!}
                                          </div>
                                      </div>
                                  </div>
                               @endif

                             <div id="loader" class="text-center d-none">
                                 <img src="{{asset('img/loader.gif')}}" alt="loader" class="img-fluid">
                                 <h6>Идет обработка запроса</h6>
                             </div>
                             <form action="{{route('processHandler')}}" id="processForm" method="POST" enctype="multipart/form-data">

                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="entity_id">Выберите тип сущности</label>
                                            <select name="entity_id" id="entity_id" class="form-control {{isset(request('requestErrors')['entity_id']) ? ' is-invalid ':''}}">
                                                <option value="">Выбрать</option>
                                                @foreach(\App\Models\Entity::pluck('title','id') as $id=>$title)
                                                    <option
                                                        value="{{$id}}" {{old('entity_id') === $id ? 'selected':''}}>{{$title}}</option>
                                                @endforeach
                                            </select>
                                            @if(isset(request('requestErrors')['entity_id']))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{request('requestErrors')['entity_id'][0] }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="file">Файл</label>
                                            <div class="custom-file">
                                                <input type="file" name="file" class="custom-file-input {{isset(request('requestErrors')['file']) ? ' is-invalid ':''}}" id="file">
                                                <label class="custom-file-label" for="file">Выбрать файл</label>
                                            </div>
                                            @if(isset(request('requestErrors')['file']))
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{request('requestErrors')['file'][0] }}</strong>
                                            </span>
                                            @endif
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
                                @if(request('success'))
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="alert alert-success mb-5">
                                                {{request('success')}}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                    @if(request('message'))
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="alert alert-danger mb-5">
                                                    {!! request('message') !!}
                                                </div>
                                            </div>
                                        </div>
                                    @endif
{{--                               <script>--}}
{{--                                   setTimeout(() => location.href='{{route('home')}}', 60000);--}}
{{--                               </script>--}}
                                <div class="row mt-5">
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-body history-card">
                                                <div id="titleContainer">
                                                    <h4 id="historyTitle" class="d-inline">Идет выполнение процесса...</h4>
{{--                                                    <button
                                                        onclick="location.href='{{route('home')}}'"
                                                        class="btn btn-sm btn-success d-inline ml-3 mr-3">
                                                        <i class="fa fa-sync mr-2"></i>
                                                        обновить
                                                    </button>--}}
                                                    <form action="{{route('processTerminate')}}" method="POST" class="d-inline">

                                                    <button
                                                        type="submit"
                                                        onclick="return confirm('Вы уверены, что хотите прервать процесс?')"
                                                        id="terminateProcessBtn" class="btn btn-sm btn-danger d-inline">
                                                        <i class="fa fa-minus-circle mr-2"></i>
                                                        прервать процесс
                                                    </button>
                                                    </form>
                                                </div>
                                                <hr>
                                                <p>Время запуска: <span id="process_start"> {{$lastProcess->process_start}}</span></p>
                                                <p>Плановое время завершения: <span id="process_end"> {{$lastProcess->process_end}}</span></p>
                                                <p>Тип сущности: <span id="entity_title"> {{$lastProcess->entity->title}}</span></p>
                                                <p>Обработано: <span id="lines_success"> {{$lastProcess->lines_success}} </span> <span>  из</span><span id="lines_count"> {{$lastProcess->lines_count}}</span><span id="line_processed_percent"> ({{round(($lastProcess->lines_success/$lastProcess->lines_count)*100,2)}}%)</span></p>
                                                <p>Осталось: <span id="time_to_finish"></span></p>
                                                <p>Некритичных ошибок: <span id="lines_error"> {{$lastProcess->lines_error}}</span></p>
                                                <p>Ссылка на лог ошибок: <a href="{{route('getLog')}}" >лог ошибок</a></p>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <script>


                                    var interval =  setInterval(getSuccessCount,1000);

                                    function getSuccessCount(){

                                        $.ajax({
                                            url: "{{route('getSuccessCount')}}",
                                            type: "post",
                                            success: function (response) {
                                                if (response.processing === 1){
                                                    $('#lines_success').text(response.countSuccess);
                                                    $('#lines_error').text(response.countError);
                                                    $('#time_to_finish').text(response.timeToFinish);
                                                    var percent = ((response.countSuccess/response.count)*100).toFixed(2);
                                                    $('#line_processed_percent').text(' ('+percent+'%)');
                                                }


                                                if(response.processing === 3){
                                                   setTimeout(location.href='{{route('home')}}',2000);
                                                   clearInterval( interval );
                                                }

                                            }
                                        });


                                    }




                                </script>

                            @endif


                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $('#startBtn').click(function () {
            $('#loader').removeClass('d-none').addClass('d-block');
            $('#processForm').addClass('d-none');
        });
    </script>


@endsection
