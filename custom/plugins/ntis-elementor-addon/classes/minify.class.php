<?php

class NTIS_Minify
{
    public function css($source, $destination)
    {
        if(!$this->should_regenerate_minified_file($source, $destination)) {
            return;
        }
        $css = file_get_contents($source);
        if ($css === false) {
            return false;
        }
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        $css = str_replace(["\r\n", "\r", "\n", "\t"], '', $css);
        $css = preg_replace('/\s+/', ' ', $css);
        $css = trim($css);
        return file_put_contents($destination, $css) !== false;
    }
    public function js($source, $destination)
    {
        if(!$this->should_regenerate_minified_file($source, $destination)) {
            return;
        }
        $js = file_get_contents($source);
        if ($js === false) {
            return false;
        }
        $js = preg_replace('~//[^\r\n]*|/\*.*?\*/~s', '', $js);
        $js = str_replace(["\r\n", "\r", "\n", "\t"], '', $js);
        $js = preg_replace('/\s+/', ' ', $js);
        $js = trim($js);
        return file_put_contents($destination, $js) !== false;
    }
    private function should_regenerate_minified_file($source_path, $minified_path)
    {

        if (!file_exists($source_path)) {
            return false;
        }
        if (!file_exists($minified_path)) {
            return true;
        }
        // $source_mtime = filemtime($source_path);
        // $minified_mtime = filemtime($minified_path);
        // return ($source_mtime > $minified_mtime);
        return true;
    }
}
