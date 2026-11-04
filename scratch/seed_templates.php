<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.default' => 'sqlite']);
config(['database.connections.sqlite.database' => database_path('database.sqlite')]);
\Illuminate\Support\Facades\DB::purge();

use App\Models\Language;
use App\Models\Template;

$languages = [
    'English (United Kingdom)' => 'en-GB',
    'Serbian' => 'sr',
    'Polish' => 'pl',
    'Arabic' => 'ar',
    'English 1' => 'en-1',
    'English 2' => 'en-2',
    'English 3' => 'en-3',
];

$langModels = [];
foreach ($languages as $name => $code) {
    $langModels[$name] = Language::firstOrCreate(['name' => $name], [
        'code' => $code,
        'version' => 0,
    ]);
}

$annexHtml = <<<HTML
<html>
<body vlink="blue" link="blue">
<div id="content" style="position:relative; margin:0px; padding:0px; height:267mm; width:180mm; font-family:Arial,Helvetica,sans-serif; font-size:8pt; background-color:white; color:#000000;">
<h2 id="title" style="text-align:center; font-weight:900;">ANNEX VII</h2>
<p style="text-align:center;">
INFORMATION ACCOMPANYING SHIPMENTS OF WASTE AS REFERRED TO IN ARTICLE 3(2) AND (4)<br/>
(revised version as per Official Journal of the European Union 22.12.2020 L433/13) REGULATION (EU) 2020/2174
</p>
<p style="text-align:center; font-weight:bold;">
MEX2023059 - xxxxx - BL No. xxxxxxx - MSC
</p>
<span style="font-weight:bold;">
Consignment Information (1)
</span>
<table border="0" cellpadding="0" cellspacing="0" align="center" style="border-collapse:collapse; width:100%; font:inherit;">
<tr>
<td style="padding:1mm; border:1px solid black; vertical-align:top; width:50%;">
<span style="font-weight:bold;">1. Person who arranges the shipment:</span><br/>
Name: <span>METAENTERPRICES BULGARIA LTD</span><br/>
Address: <span>GR PLOVDIV UL. KNYAZ BOGORIDI 5 ET 2</span><br/>
Contact Person: <span>Ivena Milenovic</span><br/>
Tel: <span>+359(0) 887 931 514</span><br/>
Email: <span>iva@metaenterprices.co.uk</span>
</td>
<td style="padding:1mm; border:1px solid black; vertical-align:top; width:50%;">
<span style="font-weight:bold;">2. Consignee:</span><br/>
Name: <span>RUBAMIN PRIVATE LIMITED</span><br/>
Address: <span>PLOT NO. 100, NANDESARI NOTIFIED AREA NANDESARI -391340, DIST. VADODARA GUJARAT, INDIA</span><br/>
Contact Person: <span>Akhilesh Singh</span><br/>
Tel: <span>+39069251942</span><br/>
EMAIL: <span>akhilesh.singh@rubamin.com</span>
</td>
</tr>
<tr>
<td style="padding:1mm; border:1px solid black; vertical-align:top; width:50%;">
3. Actual quantity: <span>XXXX</span> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Tonnes(Mg) m3: <span>XXXX</span>
</td>
<td style="padding:1mm; border:1px solid black; vertical-align:top; width:50%;">
4. Actual date of shipment: <span>20/04/2023</span>
</td>
</tr>
<tr>
<td style="padding:1mm; border:1px solid black; vertical-align:top;">
<span style="font-weight:bold;">5.(a) First Carrier</span><br/>
Name:<br/>
Address:<br/>
Contact Person:<br/>
Tel:<br/>
FAX:<br/>
E-mail:<br/>
Means of Transport:<br/>
Date of Transfer:<br/>
Signature:
</td>
<td style="padding:0; border:1px solid black; vertical-align:top;">
<table style="width:100%; border-collapse:collapse; height:100%; font:inherit;">
<tr>
<td style="border-right:1px solid black; width:50%; vertical-align:top; padding:1mm;">
<span style="font-weight:bold;">5.(b) Second carrier</span><br/>
Name:<br/>
Address:<br/>
Tel:<br/>
E-mail:<br/>
Means of Transport:<br/>
Date of transfer:<br/>
Signature:
</td>
<td style="border:none; vertical-align:top; padding:1mm; width:50%;">
<span style="font-weight:bold;">5.(c) Third carrier</span><br/>
Name:<br/>
Address:<br/>
Contact Person:<br/>
Tel:<br/>
FAX:<br/>
E-mail:<br/>
Means of Transport:<br/>
Date of Transfer:<br/>
Signature:
</td>
</tr>
</table>
</td>
</tr>
</table>
</div>
</body>
</html>
HTML;

$templateList = [
    'English (United Kingdom)',
    'Serbian',
    'Polish',
    'Arabic',
    'English 1',
    'English 2',
    'English 3',
];

foreach ($templateList as $langName) {
    $lang = $langModels[$langName];
    $tpl = Template::where('name', 'ANNEX_VII')
        ->where('language_id', $lang->id)
        ->first();

    if (!$tpl) {
        Template::create([
            'name' => 'ANNEX_VII',
            'type' => 'ANNEX_VII',
            'language_id' => $lang->id,
            'html' => $annexHtml,
            'keys' => '',
            'version' => 1,
        ]);
    } else {
        $tpl->update([
            'type' => 'ANNEX_VII',
            'html' => $annexHtml,
        ]);
    }
}

echo "Successfully seeded ANNEX_VII templates for all 7 languages!\n";
