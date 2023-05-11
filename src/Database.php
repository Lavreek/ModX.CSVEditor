<?php

class Database
{
    private $mysql;

    public function __construct($host, $user, $password, $dbname)
    {
        $this->mysql = new mysqli($host, $user, $password, $dbname);
    }

    public function getRequest(string $query)
    {
        return $this->mysql->query($query);
    }

    private function getInsertedId()
    {
        return mysqli_insert_id($this->mysql);
    }

    private function returnResponse($request, bool $single = false)
    {
        if (mysqli_num_rows($request) > 0) {
            if ($single) {
                return mysqli_fetch_array($request, MYSQLI_ASSOC);
            }

            return mysqli_fetch_all($request, MYSQLI_ASSOC);
        } else {
            return null;
        }
    }

    public function getResourcesByParent(int $id)
    {
        $query = "SELECT `id`, `pagetitle` FROM `modx_site_content` WHERE `parent` = $id";
        $request = $this->getRequest($query);

        return $this->returnResponse($request);
    }

    public function getResourcesByPagetitle(string $pagetitle, string $condition = "`id`, `pagetitle`")
    {

        $query = "SELECT $condition FROM `modx_site_content` WHERE `pagetitle` = '$pagetitle'";
        $request = $this->getRequest($query);

        return $this->returnResponse($request, single: true);
    }

    public function getTemplateById(int $contentid, int $id)
    {
        $query = "SELECT * FROM `modx_site_tmplvar_contentvalues` WHERE `tmplvarid` = $id AND `contentid` = $contentid";
        $request = $this->getRequest($query);

        return $this->returnResponse($request, single: true);
    }

    public function getTemplatesByContentId(int $id) : array|null
    {
        $query = "SELECT * FROM `modx_site_tmplvar_contentvalues` WHERE `contentid` = $id";
        $request = $this->getRequest($query);

        return $this->returnResponse($request);
    }

    public function updateTemplateById(int $id, string $value) : void
    {
        $query = "UPDATE `modx_site_tmplvar_contentvalues` SET `value` = '$value' WHERE `id` = $id";
        $request = $this->getRequest($query);
    }

    public function createTemplateByContentId(int $contentid, int $tmplvarid, string $value) : void
    {
        $query = "INSERT INTO `modx_site_tmplvar_contentvalues` (`contentid`, `tmplvarid`, `value`) VALUES ('$contentid', '$tmplvarid', '$value')";
        $this->getRequest($query);
    }

    public function createResourceSkeleton($resource)
    {
        $file_name = ROOT_PATH . "/" . $_ENV['FILE_NAME'] . ".json";

        if (!file_exists($file_name)) {
            $resource = $this->getResourcesByPagetitle($resource['pagetitle'], "*");

            $resource['id'] = "NULL";
            $resource['pagetitle'] = uniqid().time();

            file_put_contents($file_name, json_encode($resource));
        }
    }

    public function createResourceBySkeleton(string $pagetitle)
    {
        $file_name = ROOT_PATH . "/" . $_ENV['FILE_NAME'] . ".json";

        if (file_exists($file_name)) {
            $resource = json_decode(file_get_contents($file_name), true);
            $resource['pagetitle'] = $pagetitle;

            $cols = $values = [];

            foreach ($resource as $key => $param) {
                array_push($cols, $key);
                array_push($values, $param);
            }

            $query = "INSERT INTO `modx_site_content` (`". implode("`, `", $cols) ."`) VALUES ('" . implode("', '", $values) . "')";
            $this->getRequest($query);

            $resource['id'] = $this->getInsertedId();

            return $resource;
        }

        return null;
    }
}