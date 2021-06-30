<script>
    $('#startBtn').click(function(e){
        e.preventDefault();
        $('.invalid-feedback').remove();
        $('input').removeClass('is-invalid');
        $('select').removeClass('is-invalid');


        var formData = new FormData;
        var entity_id = $('#entity_id').val();
        var file = $('#file').prop('files')[0];

        formData.append('_token', '{{csrf_token()}}');
        if(file!=null){
            formData.append('file', file);
        }

        formData.append('entity_id', entity_id);


        $.ajax({
            type: 'POST',
            url: '{{route('processHandler')}}',
            data: formData,
            cache : false,
            contentType: false,
            processData : false,
            success: function(result){
                if(result.errors){
                    $.each(result.errors, function (i,val) {

                        $('#'+i).addClass('is-invalid')
                        .parent().append('<span class="invalid-feedback d-block">'+val[0]+'</span>')
                    });
                }

                if(result.success){
                    console.log(result.success.message);
                    var process_data = result.success.process_data;
                    console.log(process_data);
                    $('#startBtn').addClass('disabled').attr('disabled',true);
                    $('#historyTitle').text('Идет выполнение процесса...');
                    $('#titleContainer').append('<button onclick="updateProcess();" class="btn btn-sm btn-success d-inline ml-3 mr-3"><i class="fa fa-sync mr-2"></i>обновить</button><button data-uid = "'+ process_data.uid.toString() + '" onclick="terminateProcess();" id="terminateProcessBtn" class="btn btn-sm btn-danger d-inline"><i class="fa fa-minus-circle mr-2"></i>прервать процесс</button>');
                    $('#process_start').text(process_data.process_start);
                    $('#process_end').text(process_data.process_end);
                    $('#entity_title').text(process_data.entity_title);
                    $('#lines_count').text(process_data.lines_count);
                    $('#lines_success').text(process_data.lines_success);
                    $('#lines_error').text(process_data.lines_error);


                }
            },
            error: function (error) {
                console.log(error);
            }
        });
    });

    function updateProcess() {
        console.log('Обновление процесса!');
        var count = parseInt($('#lines_count').text());
        var errors = parseInt($('#lines_error').text());
        var lines_success = parseInt($('#lines_success').text());
        if(lines_success< count-errors){
            $('#lines_success').text(lines_success+15);
        }

    }

    function terminateProcess() {
        if(confirm('Вы уверены?')){
            console.log('Остановка процесса!');
            var uid = $('#terminateProcessBtn').attr('data-uid');
            var lines_success = parseInt($('#lines_success').text());
            var formData2 = new FormData;
            formData2.append('_token', '{{csrf_token()}}');
            formData2.append('lines_success', lines_success);
            formData2.append('uid', uid);

            $.ajax({
                type: 'POST',
                url: '{{route('processTerminate')}}',
                data: formData2,
                cache : false,
                contentType: false,
                processData : false,
                success: function(result){
                    if(result.success){
                        console.log(result.success);

                        location.reload();
                    }
                },
                error: function (error) {
                    console.log(error);
                }
            });
        }



    }
</script>
