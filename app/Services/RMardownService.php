<?php
namespace App\Services;
use Response;

class RMarkdownService {

    /**
     * Constructor
     * @param void
     * @return void
     */
    public function __construct() {
        $this->documentRoot = $_SERVER['DOCUMENT_ROOT'];
    }

    /**
     * Generate Head of RMarkdown File
     * @param $title, 
     * @return void
     */
    public function generateTitle($title) {
        $fp = fopen("$this->documentRoot/test/bar.Rmd", 'wb');
        $HeadOfRmdFile =
        "---\n"
        ."title: \"{$title}\"\n"
        ."output:\n"
        ."flexdashboard::flex_dashboard:\n"
        ."    orientation: rows\n"
        ."    social: menu\n"
        ."---\n"
        ."```{r setup, include=FALSE}\n"
        ."library(flexdashboard)\n"
        ."library(dygraphs)\n"
        ."library(xts)\n";
        fwrite($fp, $HeadOfRmdFile, strlen($HeadOfRmdFile));
        fclose($fp);
    }

    /**
     * Write R scripts for each appearance occasion
     * eg. "event", "work", "student", "invite", etc.. 
     * 
     * @param $firstYear
     * @param $lastYear
     * @param $firstMonth
     * @param $lastMonth
     * @param $appearances
     * @param $key
     * 
     * @return void
     * 
     */
    public function generateMemberAppearancesRmd(
        $firstYear, 
        $lastYear, 
        $firstMonth, 
        $lastMonth, 
        $appearances, 
        $key,
        $limit
    ) {
        // Open RMarkdown file to append
        $fp = fopen("$this->documentRoot/test/bar.Rmd", 'ab');

        static $count; // to keep track of function calls

        if ($count !== $limit) {
            // insert data into R script for each occasion
            $appendString = 
            "$key.ByMonthYear <- c(".$this->extractDataFromArray($appearances)."\n"
            ."$key.TS <- ts( $key.ByMonthYear, start = c({$firstYear},{$firstMonth}), end = c({$lastYear},{$lastMonth}), frequency = 12)\n"
            ."$key.TS_AS_XTS <- as.xts($key.TS)\n\n";

            $count++;

            // append R script to file 
            fwrite($fp, $appendString, strlen($appendString));
        } 
        // on last function call 
        else if ($count === $limit) {
            // end R script
            fwrite($fp, "```\n", strlen("```\n") );
        }
        fclose($fp);
    }    

   // extract and format values from array to print in an R array c(1,2,3,4....)
   private function extractDataFromArray($data) {
        $extractedData = "";
        foreach ($data as $datum) {
            // format 1,2,3,...
            $extractedData = ltrim($extractedData.','.$datum, ',');
        }
        return $extractedData;
    }
}