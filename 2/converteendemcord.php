<!DOCTYPE html>
<html>
<head>
  <title>App</title>
</head>
<body>
<script src="https://unpkg.com/axios/dist/axios.min.js" type="text/javascript"></script>
<script type="text/javascript">
const API_URL = 'https://maps.googleapis.com/maps/api/geocode/json';
const API_KEY = 'AIzaSyCMh-I_OgUOqWmr884bNUgwH8bVci6xY_4';

const doRequest = (url) => {
  const promisseCallback = (resolve, reject) => {
    axios.get(url).then((result)=> {
      resolve(result.data);
    }).catch(reject);
  };
  return new Promise(promisseCallback);
}

const getApiUrl = (address) => {
  return `${API_URL}?key=${API_KEY}&address=${encodeURI(address)}`;
}

const address = '<?php echo $_GET['endereco'] ?>, <?php echo $_GET['n'] ?>, <?php echo $_GET['bairro'] ?>, <?php echo $_GET['cidade'] ?>, <?php echo $_GET['uf'] ?>';

(async () => {
  const apiUrl = getApiUrl(address);
  const data = await doRequest(apiUrl);
  
  if (!data || data.error_message) {
    const message = (data && data.error_message) ? data.error_message : 'Api Error';
    console.log(message);
    return;
  }
  
 // console.log(data.results[0].geometry.location);

  window.location.href="salvaend.php?cep=<?php echo $_GET['cep'] ?>&endereco=<?php echo $_GET['endereco'] ?>&n=<?php echo $_GET['n'] ?>&bairro=<?php echo $_GET['bairro'] ?>&cidade=<?php echo $_GET['cidade'] ?>&uf=<?php echo $_GET['uf'] ?>&latitude=" + data.results[0].geometry.location.lat + "&longitude=" + data.results[0].geometry.location.lng;

})();

</script>
