<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;

class AddApi extends Command
{
    private $content = '<?php

namespace App\\ApiSource\\NAME;

use App\\ApiSource\\Api\\Api;

class NAME extends Api
{

    function search($query, $page = 0)
    {
        $host_name = \'\';
        $this->setApiSearchLink(\'\');
        $this->setPageParameter(\'\');
        $this->setClientKey(\'\');
        
        $urls = array();
        $artists = array();
        $durations = array();
        $titles = array();
        $thumbnails = array();
        
        //
        // ...
        //
        
        return $response;

    }
}';

    protected $signature = 'api:create {name}';


    protected $description = 'Creates new Api class and registers it to config';


    public function __construct()
    {
        parent::__construct();
    }


    public function handle()
    {
        File::makedirectory(app_path() . '\\ApiSource\\' . $this->argument('name'));
        File::put(app_path() . '\\ApiSource\\' . $this->argument('name') . '\\' . $this->argument('name') . '.php', str_replace('NAME', $this->argument('name'), $this->content ));
        $configs = config('api.apis');
        $configs = array_push($configs, 'App\\\ApiSource\\' . $this->argument('name') . '\\' . $this->argument('name'));
        Config::set('api.apis', $configs);
        echo 'Api class created successfully';
    }
}
