<?php

$FileHandle = fopen('result.pdf', 'w+');

$curl = curl_init();

$instructions = '{
  "parts": [
    {
      "file": "document"
    }
  ]
}';

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://api.pspdfkit.com/build',
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_POSTFIELDS => array(
    'instructions' => $instructions,
    'document' => new CURLFILE('test123.docx')
  ),
  CURLOPT_HTTPHEADER => array(
    'Authorization: pdf_live_TmQ7f22G8EOQMfaurKC7SJETbV4jDstApBlUXfAVbMS'
  ),
  CURLOPT_FILE => $FileHandle,
));

$response = curl_exec($curl);

curl_close($curl);

fclose($FileHandle);