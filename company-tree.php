 
<?php

class Travel
{
    private $travels = [];

    public function __construct($travels) {
        // Initialise the travels property with the company id and sum the price.
        foreach ($travels as $travel) {
            if (!isset($this->travels[$travel->companyId])) {
                $this->travels[$travel->companyId] = 0;
            }
            $this->travels[$travel->companyId] += $travel->price;
        }
    }

    public function getTravelCost($companyId) {
        return $this->travels[$companyId] ?? 0;
    }
}

class Company
{
    private $companies = [];
    private $travel;

    public function __construct($companies, $travel) {
        // Get the travel cost per company from the Travel class.
        $this->travel = $travel;
        // Initialise an array of companies keyed by their parent company.
        foreach ($companies as $company) {
            $company->children = [];
            $company->cost = 0;
            $this->companies[$company->parentId][] = $company;

        }
    }

    public function buildCompanyTree($parentId = "0") {
        $result = [];
        // Return empty if the parent id is not found in the list of companies.
        if (!isset($this->companies[$parentId])) {
            return $result;
        }
        // Loop over all companies under the current parent id.
        foreach ($this->companies[$parentId] as $company) {
            $company->children = $this->buildCompanyTree($company->id);
            $company->cost = $this->travel->getTravelCost($company->id);
            foreach ($company->children as $child) {
                // Add costs for each child.
                $company->cost += $child->cost;
            }
            $result[] = $company;
        }
        return $result;
    }
}

class TestScript
{
    public function execute()
    {
        $start = microtime(true);
        // Get the API data.
        $companyData = json_decode(file_get_contents('https://5f27781bf5d27e001612e057.mockapi.io/webprovise/companies'));
        $travelData = json_decode(file_get_contents('https://5f27781bf5d27e001612e057.mockapi.io/webprovise/travels'));
        // Create objects using the data.
        $travel = new Travel($travelData);
        $company = new Company($companyData, $travel);
        $result = $company->buildCompanyTree();
        echo json_encode($result);
        echo 'Total time: '.  (microtime(true) - $start);
    }
}

(new TestScript())->execute();
