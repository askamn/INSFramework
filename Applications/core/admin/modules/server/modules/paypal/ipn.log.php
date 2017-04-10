<?php
/* Edited by AskAmn to work with PayPal IPN Module */

header('content-type: text/plain; charset=utf-8');

function tail($filename, $lines = 10, $buffer = 4096)
{
        $f = fopen($filename, "rb");
        fseek($f, -1, SEEK_END);
        if(fread($f, 1) != "\n")
                $lines -= 1;
        $output = '';
        $chunk = '';

        while(ftell($f) > 0 && $lines >= 0)
        {
                $seek = min(ftell($f), $buffer);
                fseek($f, -$seek, SEEK_CUR);
                $output = ($chunk = fread($f, $seek)).$output;
                fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);
                $lines -= substr_count($chunk, "\n");
        }

        while($lines++ < 0)
                $output = substr($output, strpos($output, "\n") + 1);

        fclose($f); 
        return $output; 
}

/* Reverse tail the log */
$log = array_reverse(array_filter(explode("\n", tail('ipn.log.txt'))));

/* Parse the entries */
foreach($log as &$entry)
{
        $entry = explode("\t", $entry);
        $entry[1] = json_decode($entry[1], true);
}

/* Produce output */
foreach($log as $entry)
{
        $data = '';
        foreach($entry[1] as $key => $value)
                $data .= '<tr><th>'
                                .htmlspecialchars(ucwords(str_replace('_', ' ', $key)))
                                .'</th><td>'
                                .htmlspecialchars($value)
                                .'</td></tr>';
                
        printf('<table>'
                .  '<thead><tr><th colspan="2"><button class="detail-link">Details</button><span>%s</span></th></tr></thead>'
                .  '<tbody>%s</tbody>'
                .  '</table>',
                date('Y-m-d H:i:s P', $entry[0]),
                $data);
}
?>