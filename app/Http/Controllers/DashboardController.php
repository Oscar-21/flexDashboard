<?php
namespace App\Http\Controllers;

use Response;
use Auth;
use JWTAuth;

// Service Classes 
use App\Services\AppearanceService;
use App\Services\JoinsService;

// Eloquent Models
use App\User;
use App\Appearance;

class DashBoardController extends Controller {

    // Inherited Classes
    protected $appearanceService;
    protected $joinsService;


    public function __construct(
        AppearanceService $appearanceService, 
        JoinsService $joinsService 
    ) {

        $this->documentRoot = $_SERVER['DOCUMENT_ROOT'];
        $this->appearanceService = $appearanceService;
        $this->joinsService = $joinsService;

        // authentication 
        $this->middleware('jwt.auth', [
            'only' => [
                'allUserJoins',
                'spaceAppearances',
            ]   
        ]);

    }

   // format values to print in R array c(1,2,3,4....)
   private function extractDataFromArray($data) {
        $extractedData = "";
        foreach ($data as $datum) {
            // format 1,2,3,...
            $extractedData = ltrim($extractedData.','.$datum, ',');
        }
        return $extractedData;
    }

    /**
     * Generate member signup data graph
     */
    private function generateMemberJoinsRmd(
        $firstYear, 
        $lastYear, 
        $firstMonth, 
        $lastMonth, 
        $joins
    ) {
        $fp = fopen("$this->documentRoot/test/foo.Rmd", 'wb');

        // R markdown formatted string
        $outputString = 
            "---\n"
            ."title: \"BAr Sign ups {$firstYear}-{$lastYear}\""
            ."\noutput:\n" 
            ."  flexdashboard::flex_dashboard:\n"
            ."    orientation: rows\n"
            ."    social: menu\n"
            ."---\n\n"
            ."```{r setup, include=FALSE}\n"
            ."library(flexdashboard)\n"
            ."library(dygraphs)\n"
            ."library(xts)\n"
            ."joinsByMonthYear <-" 
            ." c(".$this->extractDataFromArray($joins).")\n"
            ."joinTS <- ts( joinsByMonthYear, start= c({$firstYear},{$firstMonth}), end= c({$lastYear},{$lastMonth}), frequency = 12)\n"
            ."joinTS_AS_XTS <- as.xts(joinTS)\n"
            ."```\n"
            ."Row {.tabset .tabset-fade}\n"
            ."-------------------------------------\n"
            ."### All incubators\n"
            ."```{r}\n"
            ."dygraph(joinTS_AS_XTS) %>%\n" 
            ."dyOptions(drawPoints = TRUE, pointSize = 2) %>%\n"
            ."dyRangeSelector()\n"  
            ."```\n";
        fwrite($fp, $outputString, strlen($outputString) );
        fclose($fp);
    }


    /**
     * Generate Title to appearance .Rmd
     */
    private function generateMemberAppearancesStartRmd(
        $firstYear, 
        $lastYear
    ) {
        $fp = fopen("$this->documentRoot/test/bar.Rmd", 'wb');

        function begin($firstY, $lastY) {
            $startFile =
            "---\n"
            ."title: \"Appearances!? {$firstY}-{$lastY}\"\n"
            ."output:\n"
            ."flexdashboard::flex_dashboard:\n"
            ."    orientation: rows\n"
            ."    social: menu\n"
            ."---\n"
            ."```{r setup, include=FALSE}\n"
            ."library(flexdashboard)\n"
            ."library(dygraphs)\n"
            ."library(xts)\n";
            return $startFile;
        }

        $firstBlock = begin($firstYear, $lastYear);
        fwrite($fp, $firstBlock, strlen($firstBlock) );
        fclose($fp);
    }


    /**
     * Generate data to appearance .Rmd
     */
    private function generateMemberAppearancesRmd(
        $firstYear, 
        $lastYear, 
        $firstMonth, 
        $lastMonth, 
        $appearances, 
        $key
    ) {
        $fp = fopen("$this->documentRoot/test/bar.Rmd", 'ab');
        // keep track of function calls
        static $count; 
        $lastCall = 5;

        $appendString = 
        "$key.ByMonthYear <- c(".$this->extractDataFromArray($appearances)."\n"
        ."$key.TS <- ts( $key.ByMonthYear, start = c({$firstYear},{$firstMonth}), end = c({$lastYear},{$lastMonth}), frequency = 12)\n"
        ."$key.TS_AS_XTS <- as.xts($key.TS)\n\n";
        $count = $count + 1;

        fwrite($fp, $appendString, strlen($appendString) );
        // on last function call 
        if ($count == $lastCall) {
            fwrite($fp, "```\n", strlen("$```\n") );
        }
        fclose($fp);
    }

    /**
     * Get data and write R markdown file.
     * @param workspace.id 
     * @return Illuminate\Support\Facades\Response::class
     */    public function Joins($spaceId) {
        /**
         * Get sorted member joins and dates
         */
        $data = $this->joinsService->spaceUserJoins($spaceId);
        $n = count($data);
        $firstYear = $data[$n - 4]; 
        $lastYear = $data[$n - 3];
        $firstMonth = $data[$n - 2];
        $lastMonth = $data[$n - 1];
        $sortedJoins = array_slice($data, 0, ($n - 5));

      $this->generateMemberJoinsRmd(
          $firstYear, 
          $lastYear, 
          $firstMonth, 
          $lastMonth, 
          $sortedJoins
        );
    }

    /**
     * Get all appearances 
     * @param $spaceId
     * @return Illuminate\Support\Facades\Response::class
     */
    public function Appearances($spaceId) {
        $appearances = array(
            'all' => $this->appearanceService->getAllAppearances($spaceId), 
            'event' => $this->appearanceService->getEventAppearances($spaceId),
            'work' => $this->appearanceService->getNonEventAppearances($spaceId, 'work'),
            'booking' => $this->appearanceService->getNonEventAppearances($spaceId, 'booking'),
            'student' => $this->appearanceService->getNonEventAppearances($spaceId, 'student'),
            'invite' => $this->appearanceService->getNonEventAppearances($spaceId, 'invite')
        );

        //$n = count($appearances);
        $count = 0;
        foreach ($appearances as $key => $appearance) {
            $n = count($appearance);
            $firstYear = $appearance[$n - 4]; 
            $lastYear = $appearance[$n - 3];
            $firstMonth = $appearance[$n - 2];
            $lastMonth = $appearance[$n - 1];
            $sortedAppearances = array_slice($appearance, 0, ($n - 5));
            
            if ($count == 0) {
                $this->generateMemberAppearancesStartRmd(
                    $firstYear, 
                    $lastYear 
                );
                $count++;
            } else {
                $this->generateMemberAppearancesRmd(
                    $firstYear, 
                    $lastYear, 
                    $firstMonth, 
                    $lastMonth, 
                    $sortedAppearances, 
                    $key
                );
            }
        }

    }

    public function inviteHelper() {

    }
}
