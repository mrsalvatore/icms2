<?php

class cmsUpdater {

    private $update_info_url = 'http://upd.instantcms.ru/info/%s';
    private $cache_file = 'cache/update.dat';

    const UPDATE_CHECK_ERROR = 0;
    const UPDATE_DOWNLOAD_ERROR = 1;
    const UPDATE_NOT_AVAILABLE = 2;
    const UPDATE_AVAILABLE = 3;
    const UPDATE_SUCCESS = 4;

    public function __construct() {
        $this->cache_file = cmsConfig::get('root_path') . $this->cache_file;
    }

    public function checkUpdate($only_cached=false){

        $current_version = cmsCore::getVersion();

        $update_info = $this->getUpdateFileContents($current_version, $only_cached);

        if (!$update_info) { return cmsUpdater::UPDATE_CHECK_ERROR; }

        list($next_version, $date, $url) = explode("\n", trim($update_info));

        if (version_compare($next_version, $current_version, '<=')) {
            $this->deleteUpdateFile();
            return cmsUpdater::UPDATE_NOT_AVAILABLE;
        }

        return array(
            'version' => $next_version,
            'date' => $date,
            'url' => $url
        );

    }

    public function getUpdateFileContents($current_version, $only_cached){

        if (file_exists($this->cache_file)){
            return file_get_contents($this->cache_file);
        } else if ($only_cached) {
            return false;
        }

        $url = sprintf($this->update_info_url, $current_version);

        $data = file_get_contents_from_url($url);

        if ($data === false) { return false; }

        file_put_contents($this->cache_file, $data);

        return $data;

    }

    public function deleteUpdateFile(){
        @unlink($this->cache_file);
    }

}
