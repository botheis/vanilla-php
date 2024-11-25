<?php

namespace Core{

    class Controller{

        static public function render($view, $datas=[]){
            global $session;

            $view = str_replace('/', ".", $view);
            $view = preg_replace('#\.+#', "/", $view);

            $filename = '../Views/'.$view.'.php';
            if(is_file($filename)){
                ob_start();
                require_once($filename);
                $content = ob_get_clean();

                // ======
                // Replace rules in templates
                // ======

                // _csrf
                $content = preg_replace("#@csrf#", "<input type='hidden' name='_csrf' value='".$session->get("_csrf")."' />", $content);

                // render
                $content = preg_replace_callback("#@render\((.*)\)#", function($view) use ($datas){
                    array_shift($view);
                    $view = $view[0];
                    ob_start();
                    $partial = static::render($view, $datas);
                    ob_clean();
                    return $partial;
                }, $content);

                $content = preg_replace_callback("#@data\((.*)\)#", function($keys) use ($datas){
                    array_shift($keys);
                    $keys = $keys[0];

                    $keys = explode(".", $keys);
                    $index = $datas;
                    foreach($keys as $key){
                        if(isset($index[$key])){
                            $index = $index[$key];
                            continue;
                        }
                        else{
                            break;
                        }
                    }
                    if(!is_array($index)){
                        return $index;
                    }
                    return "";
                }, $content);

                $content = preg_replace_callback("#@pagination#", function() use ($datas){
                    global $request;
                    // No pagination if there is just 1 page
                    if(!isset($datas["pagination"]["total"]) || $datas["pagination"]["total"] == 1){
                        return "";
                    }
                    $pagination = '<nav class="pagination" role="navigation" aria-label="pagination">
                    <ul class="pagination-list">';
                        for($i=1; $i<= $datas["pagination"]["total"]; $i++){
                            $isFirst = ($i == 1) ? true : false;
                            $isLast = ($i == $datas["pagination"]["total"]) ? true : false;
                            $isFarLower = ($i < $datas["pagination"]["current"]-1 && !$isFirst && !$isLast);
                            $isFarUpper = ($i > $datas["pagination"]["current"]+1 && !$isLast && !$isLast);
                            if($isFarLower || $isFarUpper) {
                                $pagination .='<li><span class="pagination-ellipsis">&hellip;</span></li>';
                                if($isFarLower){
                                    $i = $datas["pagination"]["current"]-2;
                                }
                                else if($isFarUpper){
                                    $i = $datas["pagination"]["total"]-1;
                                }
                            }
                            else{
                                $isCurrent = "";
                                if($i == $datas["pagination"]["current"]){
                                    $isCurrent = "is-current";
                                }
                                $pagination .='<li><a href="/'.$request->config("uri").'?page='.$i.'" class=" '.$isCurrent.' pagination-link">'.$i.'</a></li>';
                            }
                        }
                    $pagination .= "</ul>
                </nav>";
                return $pagination;
                }, $content);

                echo $content;
            }
            return $content;
        }

        static public function bag(){
            return [
                "pagination"=>[
                    "total"=>1,
                    "current"=>1
                ],
                "notify" => [
                    "info" => [],
                    "success" => [],
                    "warning" => [],
                    "danger" => [],
                ],
                "title"=> "",
                "bag"=>[]
            ];
        }

        static public function processPages($nb_total_elements){
            global $config;
            $maxperpage = (isset($config["display"]["maxperpage"]) && $config["display"]["maxperpage"] > 0) ? htmlentities($config["display"]["maxperpage"]) : 10;
            $process = ceil($nb_total_elements/$maxperpage);
            return ($process == 0) ? 1 : $process;
        }
    }
}