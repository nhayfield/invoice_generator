<?php
    require_once("classes/CompanyInvoiceGenerator.php");
  

    $company_info = array("Innate Decor","123 Main St.","Corona, CA 92885", "555-1212", "");
    $client_info = array("Test Sales", "299 Elk Lodge Way", "Suite 200","Contact: Bill Simon", "555-555-1212");
    $headers = array("Description","Unit Cost","Quantity","Extended");
    $data = [];
    for($i=0;$i++,$i<=100;) {
        $data[] = array("Goods and Services and Other Incidentals ".$i, "100.00", "2", "$  200.00");
    }
    $column_widths = array(110,25,20,35);
    $column_aligns = array("L","R","C","R");
    $totals = array("Subtotal:" =>"20,000.00", "Discount:" => "1,000.00", "Total Due:" => "$  19,000.00");


    $invoice = new CompanyInvoiceGenerator();
    $invoice->SetupFirstPage("0123456789", new DateTime("3 months"), realpath("images/innate-logo.gif"));
    $invoice->AddPaymentInfoSection($company_info, $client_info);
    $invoice->AutoWrappingTable($invoice->GetX(),$invoice->GetY(),$headers,$data,$column_widths,$column_aligns);
    $invoice->Ln($invoice->line_height * 2);
    $invoice->AddTextListWithLabels(130,$invoice->GetY(), $totals,"R",1,1);
    $invoice->Output();


