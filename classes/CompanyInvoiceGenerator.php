<?php
require_once(__DIR__ . "/../fpdf/fpdf.php");

/**
 * Class CompanyInvoiceGenerator
 * Created By Nathan Hayfield
 * Extends functionality of FPDF to create an easy to use Invoice PDF Generator
 */
class CompanyInvoiceGenerator extends FPDF {

    public $line_height = 5;
    public $logo_path = "";
    public $invoice_number;
    public $invoice_number_truncate_to = 10;
    public $due_date;
    public $font = "Helvetica";
    public $font_size = 10;
    public $rounded_rectangle_bg_color = "#94b8b8";
    public $table_header_bg_color = "#94b8b8";
    public $table_alternate_row_bg_color = "#c2c2a3";

    /**
     * @param String $invoice_number
     * @param DateTime $due_date
     * @param String $logo_path
     * Sets up some defaults and saves the above fields.
     */
    function SetupFirstPage(String $invoice_number, DateTime $due_date, String $logo_path = "") {
        $this->AliasNbPages(); //{nb} used in AddPageNumber() in the Footer() for total number of pages
        $this->SetFont($this->font,'',$this->font_size);
        $this->logo_path = $logo_path;
        $this->invoice_number = substr($invoice_number,0,$this->invoice_number_truncate_to);
        $this->due_date = $due_date->format("m/d/Y");
        $this->SetLineWidth(0.35);
        $this->AddPage();
    }

    /**
     * Convenience function
     */
    function SetupAdditionalPage() {
        $this->AddPage();
        $this->SetY(40);
    }

    /**
     * Convenience function
     */
    function Bold() {
        $this->SetFont($this->font,'B');
    }

    /**
     * Convenience function
     */
    function UnBold() {
        $this->SetFont($this->font,'');
    }

    /**
     * Runs automatically when AddPage() is called
     */
    function Header() {
        $this->AddLogo();
        $this->AddInvoiceInfo();
    }

    /**
     * Runs in the header.
     * If there is a valid image it will include it in the top left of the pdf on every page
     */
    function AddLogo() {
        if($this->logo_path && file_exists($this->logo_path) && getimagesize($this->logo_path)) {
            $image_height = 25;
            $this->Image($this->logo_path, null, null, 0, $image_height);
            $this->SetY($this->GetY() + $image_height);
        }else{
            $this->SetY($this->GetY() + 15);
        }
    }

    /**
     * Add the basic invoice info (date, due date and invoice number)
     */
    function AddInvoiceInfo() {

        $now = new DateTime();
        $invoice_info = array ( "Sent On:"=>$now->format('m/d/Y'),
            "Invoice No:"=>$this->invoice_number,
            "Due Date:"=> $this->due_date);
        $this->AddTextListWithLabels(-75, 6, $invoice_info);
    }


    /**
     * @param $hex_color
     * credit to http://stackoverflow.com/questions/15202079/convert-hex-color-to-rgb-values-in-php
     * This will set the fill color for cells from a hex value
     */
    function SetFillFromHex($hex_color) {
        list($r,$g,$b) = sscanf($hex_color, "#%02x%02x%02x");
        $this->SetFillColor($r,$g,$b);
    }

    /**
     * @param array $company_info -  ex. array("Innate Decor","123 Main St.","Corona, CA 92885", "555-1212", "")
     * @param array $client_info - ex. array("Test Sales", "299 Elk Lodge Way", "Suite 200","Contact: Bill Simon", "555-555-1212")
     * This will add some Rounded Rectangle Informational Sections
     */
    function AddPaymentInfoSection(Array $company_info, Array $client_info) {

        $this->SetFillFromHex($this->rounded_rectangle_bg_color);
        $this->AddBoldTitle($this->lMargin,40,"Mail To:");
        $this->RoundedRectTextBox($this->lMargin,45,75,$company_info,2,"DF");
        $this->AddBoldTitle(125,40,"Bill To:");
        $this->RoundedRectTextBox(125,45,75,$client_info,2,"DF");
    }

    /**
     * @param int $x  - x coordinate
     * @param int $y - y coordinate
     * @param array $text - ex. array("Innate Decor","123 Main St.","Corona, CA 92885", "555-1212", "")
     */
    function AddTextList(int $x, int $y,  Array $text) {
        $this->SetY($y);
        foreach($text AS $line)
        {
            $this->SetX($x);
            $this->Cell(40, $this->line_height, $line, 0, 1, "L", false, "");
        }
    }

    /**
     * @param int $x
     * @param int $y
     * @param string $title - the bolded text
     */
    function AddBoldTitle(int $x, int $y, string $title) {
        $this->SetXY($x,$y);
        $this->Bold();
        $this->Cell(35, $this->line_height, $title, 0, 1, "L", false, "");
        $this->UnBold();
    }

    /**
     * @param int $x
     * @param int $y
     * @param array $dictionary - ex. array("Subtotal:" =>"20,000.00", "Discount:" => "1,000.00", "Total Due:" => "$  19,000.00")
     * @param string $align_value - "L", "R", or "C", only affects the value. The label is always "R" aligned
     * @param int $border - 1 or 0
     * @param int $label_fill - 1 or 0 whether the label gets filled with the current color or not
     */
    function AddTextListWithLabels(int $x, int $y, Array $dictionary, $align_value="L", $border=0, $label_fill=0) {
        $this->SetY($y);
        foreach($dictionary AS $label => $value)
        {
            $this->SetX($x);
            $this->Bold();
            $this->Cell(35, $this->line_height, $label, $border, 0, "R",$label_fill);
            $this->UnBold();
            $this->Cell(35, $this->line_height, $value, $border, 1, $align_value);
        }
    }

    /**
     * @param Int $x
     * @param Int $y
     * @param Int $w
     * @param array $text
     * @param Float $radius
     * @param string $style - trickles down to Rounded Rect ("F","S","FD","DF")
     * Makes a text box out of Maxime's Rounded Rect fuction
     *
     */
    function RoundedRectTextBox(Int $x, Int $y, Int $w, Array $text, Float $radius=3.5, $style="") {

        $height = $this->line_height * sizeof($text) + 2 * $this->line_height;
        $this->RoundedRect($x,$y,$w,$height,$radius,$style);

        $text_x = 0.1 * $w + $x;
        $text_y = 0.16 * $height + $y;

        $this->AddTextList($text_x,$text_y,$text);
        $this->SetY($this->GetY() + $this->line_height * 3);
    }

    /* Credit to Maxime Delorme and fdpf.org scripts page for this one */
    /**
     * @param $x
     * @param $y
     * @param $w
     * @param $h
     * @param $r
     * @param string $style
     */
    function RoundedRect($x, $y, $w, $h, $r, $style = '')
    {
        $k = $this->k;
        $hp = $this->h;
        if($style=='F')
            $op='f';
        elseif($style=='FD' || $style=='DF')
            $op='B';
        else
            $op='S';
        $MyArc = 4/3 * (sqrt(2) - 1);
        $this->_out(sprintf('%.2F %.2F m',($x+$r)*$k,($hp-$y)*$k ));
        $xc = $x+$w-$r ;
        $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l', $xc*$k,($hp-$y)*$k ));

        $this->_Arc($xc + $r*$MyArc, $yc - $r, $xc + $r, $yc - $r*$MyArc, $xc + $r, $yc);
        $xc = $x+$w-$r ;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l',($x+$w)*$k,($hp-$yc)*$k));
        $this->_Arc($xc + $r, $yc + $r*$MyArc, $xc + $r*$MyArc, $yc + $r, $xc, $yc + $r);
        $xc = $x+$r ;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l',$xc*$k,($hp-($y+$h))*$k));
        $this->_Arc($xc - $r*$MyArc, $yc + $r, $xc - $r, $yc + $r*$MyArc, $xc - $r, $yc);
        $xc = $x+$r ;
        $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l',($x)*$k,($hp-$yc)*$k ));
        $this->_Arc($xc - $r, $yc - $r*$MyArc, $xc - $r*$MyArc, $yc - $r, $xc, $yc - $r);
        $this->_out($op);
    }

    /* Credit to Maxime Delorme and fdpf.org scripts page for this one */
    /**
     * @param $x1
     * @param $y1
     * @param $x2
     * @param $y2
     * @param $x3
     * @param $y3
     */
    function _Arc($x1, $y1, $x2, $y2, $x3, $y3)
    {
        $h = $this->h;
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $x1*$this->k, ($h-$y1)*$this->k,
            $x2*$this->k, ($h-$y2)*$this->k, $x3*$this->k, ($h-$y3)*$this->k));
    }

    /**
     * @param $x
     * @param $y
     * @param array $table_headers - ex. array("Description","Unit Cost","Quantity","Extended")
     * @param array $table_rows - ex. array(
     *                                          array("Goods and Services", "100.00", "2", "$  200.00"),
     *                                          array("Incidentals ", "200.00", "4", "$  800.00")
     *                                      )
     * @param array $column_widths - ex. array("L","R","C","R")
     * @param array $column_aligns - ex. array(110,25,20,35)
     * @throws Exception All array inputs must be same size. Note table_rows each row must be the same size as the others.
     */
    function AutoWrappingTable($x, $y, Array $table_headers, Array $table_rows, Array $column_widths, Array $column_aligns) {
        $all_arrays_same_size = sizeof($table_headers) === sizeof($table_rows[0]) &&
                                sizeof($table_headers) === sizeof($column_widths) &&
                                sizeof($column_widths) === sizeof($column_aligns);
        if(!$all_arrays_same_size){
            throw new Exception("All array inputs must share the same length! 
                                    Except table_rows which each row should have the same length as the others.");
        }

        $this->SetXY($x,$y);
        $filled = true;

        $this->AddHeaderRow($table_headers, $column_widths);
        foreach($table_rows AS $row) {
            $this->SetX($x);
            if($this->CheckIfNewPageNeeded()) {
                $this->AddTableRow($row, $column_widths, $column_aligns, "LRB", $filled);
                $this->SetupAdditionalPage();
                $this->SetX($x);
                $this->AddHeaderRow($table_headers, $column_widths);
            }else{
                $this->AddTableRow($row, $column_widths, $column_aligns, "LR", $filled);
            }
            $filled = !$filled;
        }

        // Closing line
        $this->SetX($x);
        $this->Cell(array_sum($column_widths),0,'','T');
    }


    /**
     * @param array $table_headers
     * @param $column_widths
     * Add the header row to the AutoWrappingTable
     */
    function AddHeaderRow(Array $table_headers, $column_widths) {
        $this->SetFillFromHex($this->table_header_bg_color);
        $this->Bold();
        for($i=0;$i<sizeof($table_headers);$i++) {
            $this->Cell($column_widths[$i], $this->line_height, $table_headers[$i], 1, 0, "C", true);
        }
        $this->SetFillFromHex($this->table_alternate_row_bg_color);
        $this->UnBold();
        $this->Ln();
    }

    /**
     * @param array $table_row
     * @param array $column_widths
     * @param array $column_aligns
     * @param $border
     * @param $filled
     *
     * Adds a row to a AutoWrappingTable
     * The column_widths and $column_aligns are per column
     * Border and Filled are currently per row
     */
    function AddTableRow(Array $table_row, Array $column_widths, Array $column_aligns, $border, $filled) {
        for($i=0;$i<sizeof($table_row);$i++) {
            $this->Cell($column_widths[$i], $this->line_height, $table_row[$i], $border, 0, $column_aligns[$i], $filled);
        }
        $this->Ln();
    }

    /**
     * @return bool
     * Used by AutoWrappingTable to decide if the last row should be placed and a new table started on the next page
     */
    function CheckIfNewPageNeeded() {
        return $this->GetY() + $this->line_height * 2 >= $this->PageBreakTrigger;
    }

    /**
     * Runs automatically when AddPage() is called
     */
    function Footer()
    {
        $this->AddPageNumber();
    }

    /**
     * Uses the {nb} alias to place the page number and how many total pages there are on the bottom of the page
     */
    function AddPageNumber() {
        $this->SetY(-15);
        $this->SetFont($this->font,'',$this->font_size);
        $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
    }

}
