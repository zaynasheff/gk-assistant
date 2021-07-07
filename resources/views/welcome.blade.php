<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Модуль массового редактирования полей</title>
</head>
<body>
<div id="name">Модуль массового редактирования полей</div>

<script src="//api.bitrix24.com/api/v1/"></script>
<script>

    BX24.init(function(){

        if(BX24.isAdmin()) {

            document.location = '{{route('home')}}'

        }

    });
</script>
</body>
</html>
