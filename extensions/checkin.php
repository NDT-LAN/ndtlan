<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Registrer deltager</title>
  <script src="<?= get_asset('js/qrscanner.js') ?>'"></script>
</head>
<body>
    <video id="preview" autoplay="true"></video>
    <button id="scan">Skann billett</button>
    <script>
      const init = async () => {
        const scanBtn = document.getElementById('scan')
        const scan = await window.createScanner(document.getElementById('preview'))
        console.log(scan)
        scanBtn.addEventListener('click', async () => {
          await scan()
        })
      }

      init()
    </script>
</body>
</html>
