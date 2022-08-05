<form id="accessTokenForm" method="GET">
    <input id="accessToken" type="hidden" value="" name="access_token" />
</form>
<script>
    document.getElementById('accessToken').value = window.location.href.match(/access_token\=[\w]+/)[0].split('=')[1];
    document.getElementById('accessTokenForm').submit();
</script>