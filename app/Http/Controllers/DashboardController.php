<?php
namespace App\Http\Controllers;

use Response;
use Auth;
use JWTAuth;

// Service Classes 
use App\Services\AppearanceService;
use App\Services\JoinsService;
use App\Services\RMarkdownService;

// Eloquent Models
use App\User;
use App\Appearance;

class DashBoardController extends Controller {

    // Inherited Classes
    protected $appearanceService;
    protected $joinsService;
    protected $rmarkdownService;


    /**
     * Constructor
     */
    public function __construct(
        AppearanceService $appearanceService, 
        JoinsService $joinsService,
        RMarkdownService $rmarkdownService 
    ) {

        // Inherited Classes
        $this->appearanceService = $appearanceService;
        $this->joinsService = $joinsService;
        $this->rmarkdownService = $rmarkdownService;

        // authentication 
        $this->middleware('jwt.auth', [
            'only' => [
                'allUserJoins',
                'spaceAppearances',
            ]   
        ]);
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
     * Generate Appearances visualizations using RMarkdown 
     * @param $spaceId
     * @return Illuminate\Support\Facades\Response::class
     */
    public function Appearances($spaceId) {

        // Write head of RMarkdown File
        $this->rmarkdownService->generateTitle("Tim");

        // Get appearances from database by occasion
        $appearances = array(
            'all' => $this->appearanceService->getAllAppearances($spaceId), 
            'event' => $this->appearanceService->getEventAppearances($spaceId),
            'work' => $this->appearanceService->getNonEventAppearances($spaceId, 'work'),
            'booking' => $this->appearanceService->getNonEventAppearances($spaceId, 'booking'),
            'student' => $this->appearanceService->getNonEventAppearances($spaceId, 'student'),
            'invite' => $this->appearanceService->getNonEventAppearances($spaceId, 'invite')
        );


        // Create a seperate dataset for each occasion
        foreach ($appearances as $key => $appearance) {
            $n = count($appearance);
            $firstYear = $appearance[$n - 4]; 
            $lastYear = $appearance[$n - 3];
            $firstMonth = $appearance[$n - 2];
            $lastMonth = $appearance[$n - 1];
            $sortedAppearances = array_slice($appearance, 0, ($n - 5));
            
            // Insert data into RMarkdown Script
            $this->rmarkdownService->generateMemberAppearancesRmd(
                $firstYear, 
                $lastYear, 
                $firstMonth, 
                $lastMonth, 
                $sortedAppearances, 
                $key,
                (count($appearances) - 1) // for keeping track of function calls
            );
        }
    }

    public function inviteHelper() {

    }
}
