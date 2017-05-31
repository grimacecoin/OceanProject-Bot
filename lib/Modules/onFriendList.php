<?php

use OceanProject\Bot\API\DataAPI;
use OceanProject\Bot\XatVariables;

$onFriendList = function (array $array) {

    if (!isset($array['v'])) {
        var_dump($array);
        return;
    }

    $bot  = OceanProject\Bot\API\ActionAPI::getBot();
    $list = explode(',', $array['v']);
    $ctx  = stream_context_create(['http' => ['timeout' => 1]]);

    $volunteers = XatVariables::getVolunteers();

    $volids = [];
    for ($i = 0; $i < sizeof($volunteers); $i++) {
        $volids[] = $volunteers[$i]['xatid'];
    }

    if (sizeof($list) > 1) {
        unset($list[0]);
        foreach ($list as $id) {
            if (in_array($id, $volids)) {
                for ($i = 0; $i < sizeof($volunteers); $i++) {
                    if ($id == $volunteers[$i]['xatid']) {
                        $regname = $volunteers[$i]['regname'];
                    }
                }
            }

            $regname = (!empty($regname) ? $regname : file_get_contents('http://xat.me/x?id=' . $id, false, $ctx));

            $online[] = [
                'regname'     => $regname,
                'xatid'       => $id,
                'isAvailable' => ($id[0] == '0') ? true : false
            ];
        }

        if (sizeof($online) > 0) {
            $foo = ['B', 'M'];
            $bar = ['000000000', '000000'];

            $sort = function ($a, $b) {
                return strcmp($a['isAvailable'], $a['isAvailable']);
            };

            usort($online, $sort);

            $isAvailableString = false;
            $string            = '';
            $cpt               = 0;

            foreach ($online as $u) {
                if ($u['isAvailable']) {
                    $cpt++;
                    $isAvailableString = true;
                }

                $string .= $u['regname'] . ' [' . str_replace($bar, $foo, $u['xatid']) . '] ';

                if ($isAvailableString && !$u['isAvailable']) {
                    if ($cpt > 1) {
                        $string .= 'are available!';
                    } else {
                        $string .= 'is available!';
                    }
                }
            }

            if (sizeof($online) - $cpt > 1) {
                $string .= 'are online!';
            } else {
                $string .= 'is online!';
            }

            $bot->network->sendMessageAutoDetection(DataAPI::get('online_command')['who'], $string, DataAPI::get('online_command')['type']);
        }
    } else {
        $bot->network->sendMessageAutoDetection(DataAPI::get('online_command')['who'], 'Offline', DataAPI::get('online_command')['type']);
    }

    DataAPI::unSetVariable('online_command');
};
