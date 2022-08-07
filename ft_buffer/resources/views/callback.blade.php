<form id="accessTokenForm" method="GET">
    @csrf
    <input id="accessToken" type="hidden" value="" name="access_token" />
    <input id="data_access_expiration_time" type="hidden" value="" name="data_access_expiration_time" />
    <input id="long_lived_token" type="hidden" value="" name="long_lived_token" />
    <input id="error" type="hidden" value="" name="error" />
    <input id="error_description" type="hidden" value="" name="error_description" />
</form>
<script>
    function getQueryVariable(variable, search) {
        var query = search.substring(1);
        var vars = query.split('&');
        for (var i = 0; i < vars.length; i++) {
            var pair = vars[i].split('=');
            if (decodeURIComponent(pair[0]) == variable) {
                return decodeURIComponent(pair[1]);
            }
        }
    }
    document.getElementById('accessToken').value = getQueryVariable('access_token', window.location.hash.replace(/^#/, '?')) || '';
    document.getElementById('data_access_expiration_time').value = getQueryVariable('data_access_expiration_time', window.location.hash.replace(/^#/, '?')) || '';
    document.getElementById('long_lived_token').value = getQueryVariable('long_lived_token', window.location.hash.replace(/^#/, '?')) || '';
    document.getElementById('error').value = getQueryVariable('error', window.location.search) || '';
    document.getElementById('error_description').value = getQueryVariable('error_description', window.location.search) || '';
    document.getElementById('accessTokenForm').submit();
</script>