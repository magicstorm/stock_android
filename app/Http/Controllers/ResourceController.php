<?php
/**
 * Created by PhpStorm.
 * User: ly
 * Date: 4/21/16
 * Time: 4:50 PM
 */

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;
use App\Models\StockBasic;
use App\Models\StockHistory;
use Illuminate\Support\Str;
use Mockery\CountValidator\Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ResourceController extends Controller
{
    /**
     * PicController constructor.
     */
    protected $colsFromBasics = ['pe', 'outstanding', 'eps'];
    protected $colsAdd = ['turnoverratio', 'changePercent'];
    protected $indexes = ["上证指数", "深圳成指"];
    public function __construct()
    {
//        $this->middleware("auth:api", ["except"=>"test"]);
    }



//        dd(bcrypt("123"));
//        file_get_contents("http://p2.wmpic.me/article/2016/03/19/1458367711_GpOvzHwr.jpg")

    public function test(){
        return view('test');
    }


    public function getImage($type, $id){
//        sleep(3);
        try{
            $image = Storage::disk(Config::get('image.image_disk'))
                ->get($this->composeImagePath($type, $id));
        }catch (NotFoundHttpException $e){
            return 'image not found...';
        };
        return $image;
    }

    public function getQuotes(Request $request){
        $json = $this->getRealTimeQuotes(
            $this->getCodes($request->input('startCode'),
                $request->input('number'))
        );
        return $this->addStockInfos($this->colsFromBasics, $json, $this->colsAdd);
    }

    /**
     * @param 
     * Request $request
     */
    public function getHistory(Request $request){
        return $this->getFormattedHistory(
            $request->input("code"), 
            $request->input("start"), 
            $request->input("end"), 
            $request->input("type")
        );
    }
    

    /**
     * helpers
     */

    /**
     * @param $code
     * @param $start
     * @param $end
     * @param $type
     * values: mon,week,day,60,5,1
     */
    
    private function getFormattedHistory($code, $start, $end, $type){
        $stockInfo = new StockBasic;
        $basic = $stockInfo->where("code", $code)->first();
        $stockHistory=null;

        if($type=="day"){
            $stockHistory = $this->getHistoryByDay($code, $start, $end);
            for($i=0;$i<count($stockHistory);$i++){
                $stockHistory[$i]->turnoverratio = (string)$this->roundDecimal(
                    $this->calculateTurnoverRatio($basic->outstanding, $stockHistory[$i]->Volume),5
                );

                $last = $this->findLastClose($code, $start, "60");
//                dd($stockHistory);
                $stockHistory[$i]->changePercent = (string)$this->roundDecimal(
                    $this->calculateChangePercent(
                    $stockHistory[$i]->Close,
                    $i==0?($last==null?$stockHistory[$i]->Open:$last->Close):$stockHistory[$i-1]->Close)
                    ,5
                );
                if(abs($stockHistory[$i]->changePercent)>0.1){
                    $stockHistory[$i]->changePercent = (string)$this->roundDecimal(
                    $this->calculateChangePercent(
                    $stockHistory[$i]->Close,
                    $stockHistory[$i]->Open)
                    ,5
                );
                }
                $stockHistory[$i]->preClose = ($i==0?($last==null?"na":$last->Close):$stockHistory[$i-1]->Close);

            }
        }

        return json_encode($stockHistory);
    }
    
    private function findLastClose($code, $start, $max){
        $last = null;
        $i = 0;
        $history = new StockHistory();
        $history->setTable($code);
        while($last==null){
            $lastDate = date('Y-m-d', strtotime($start . " -1 days"));
            $last = $history->where('date', $lastDate)->first();
            if($i>=$max){
                break;
            }
            $i+=1;
        };
        return $last;
    }


    private function getHistoryByDay($code, $start, $end){
        $history = new StockHistory();
        $history->setTable($code);
        $records = $history->where('date', ">=", $start)->where('date', '<=', $end)->get();
        return $records;
    }

    /**
     * get image path from deviceType , image type , and image id
     * @return string
     */
    private function getCodes($startCode, $number){
        $stockInfo = new StockBasic;
        $stockInfos = $stockInfo->select("code")
                    ->where("code", ">=", $startCode)
                    ->orderBy("code", 'asc')
                    ->get()
                    ->take((int)$number)
                    ->toArray();
        
        $indexes = [["code"=>"sh",], ["code"=>"sz"]];
        return array_merge($stockInfos, $indexes);
    }
    
    private function getRealTimeQuotes($requestedCodes){
        $cmd = "../app/Scripts/getRealTimeQuotes.py";
        foreach($requestedCodes as $codeObj){
            $cmd .= " " . (String)$codeObj['code'];
        }
        exec($cmd, $json, $k);
        return $json[0];
    }

    private function addStockInfos($infoNameArr, $json, $newArr){
        $jsonArr = json_decode($json);
        $i = 0;
        foreach($jsonArr as $obj){
            $stockInfo = new StockBasic;
            if($i<(count($jsonArr)-2)){
                $stockBasics = $stockInfo->where("code", $obj->code)->first();
                foreach($infoNameArr as $infoName){
                    $this->setAtt($infoName, $stockBasics, $obj);
                }
                foreach($newArr as $newInfoName) {
                    $this->addAtt($newInfoName, $stockBasics, $obj);
                }
            }
            $i+=1;
        }
        return json_encode($jsonArr);
    }
    
    
    private function calculateTurnoverRatio($outstanding, $volume){
        return $volume/($outstanding*10000);
    }
    
    private function roundDecimal($floatNumber, $n){
        return round($floatNumber*pow(10, $n))/pow(10,$n);
    }

    private function composeImagePath($type, $id){
        $path = Config::get('image.' . $type).'/' .
                Config::get('image.' . $type . '_file_name') . $id . '.jpg';
        return $path;
    }

    private function setAtt($infoName, $stockBasics, $obj){
        if($infoName=="eps"){
            $obj->$infoName = (string)$stockBasics->esp;
        }
        else{
            $obj->$infoName = (string)$stockBasics->$infoName;
        }
    }
    
    private function addAtt($infoName, $stockBasics, $obj){
        if ($infoName == "turnoverratio") {
            $obj->$infoName = (string)$this->roundDecimal(
                $this->calculateTurnoverRatio($stockBasics->outstanding, (float)$obj->volume), 5
            );
        }else if($infoName == "changePercent"){
            $obj->$infoName = (string)$this->roundDecimal(
                $this->calculateChangePercent($obj->price, $obj->pre_close), 5
            );
        }
    }
    
    private function calculateChangePercent($price, $preClose){
        return ($price-$preClose)/$preClose;
    }
    private function calculatePe($price, $eps){
        return $price/$eps;
    }


}