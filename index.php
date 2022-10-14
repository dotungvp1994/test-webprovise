<?php

class Company
{
    protected string $companyListUrl = 'https://5f27781bf5d27e001612e057.mockapi.io/webprovise/companies';

    public function getAll(): array
    {
        $companies = @file_get_contents($this->companyListUrl);

        return $companies ? json_decode($companies, true) : [];
    }
}

class Employee
{
    protected string $employeeListUrl = 'https://5f27781bf5d27e001612e057.mockapi.io/webprovise/travels';

    public function getAll(): array
    {
        $employees = @file_get_contents($this->employeeListUrl);

        return $employees ? json_decode($employees, true) : [];
    }
}

class Travel
{
    public function mapOrganization(array $companies, array $employees): array
    {
        $companiesWithCost = $this->mapEmployees($companies, $employees);
        return $this->mapCompany($companiesWithCost);
    }

    public function mapEmployees(array $companies, array $employees): array
    {
        $listCost = [];
        foreach ($employees as $employee) {
            $companyId = $employee['companyId'];
            $listCost[$companyId] += (float)$employee['price'];
        }
        foreach ($companies as $key => $company) {
            $cost = array_key_exists($company['id'], $listCost) ? $listCost[$company['id']] : 0;
            $companies[$key]['cost'] = $cost;
        }
        return $companies;
    }

    public function mapCompany(array $companies): array
    {
        return $this->getChildren($companies);
    }

    public function getChildren(array $list, $parentId = '0'): array
    {
        $newList = [];
        foreach ($list as $item) {
            if ($item['parentId'] === $parentId) {
                $children = $this->getChildren($list, $item['id']);
                $item['children'] = [];
                if ($children) {
                    $item['children'] = $children;
                    $cost = $item['cost'];
                    foreach ($children as $child) {
                        $cost += (float)$child['cost'];
                    }
                    $item['cost'] = $cost;
                }
                $newList[] = $item;
            }
        }

        return $newList;
    }
}

class TestScript
{
    public function execute()
    {
        $start = microtime(true);

        $company = new Company();
        $listCompany = $company->getAll();
        $employee = new Employee();
        $listEmployee = $employee->getAll();

        $travel = new Travel();
        echo json_encode($travel->mapOrganization($listCompany, $listEmployee), JSON_PRETTY_PRINT);
        echo '<br>';
        echo 'Total time: '.  (microtime(true) - $start);
    }
}

(new TestScript())->execute();
