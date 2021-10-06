<?php
    require_once __DIR__. '/config.php';
    require __DIR__. '\vendor\autoload.php';
    require_once __DIR__. '/Parser.php';

    require_once __DIR__ . '/PHPExcel.php';
    require_once __DIR__ . '/PHPExcel/Writer/Excel2007.php';

    use HeadlessChromium\BrowserFactory;
    use HeadlessChromium\Page;

    $browserFactory = new BrowserFactory();

    // Создаём excel файл и настраиваем
    $xls = new PHPExcel();

    $xls->getProperties()->setTitle("Парсер");
    $xls->setActiveSheetIndex(0);

    $sheet = $xls->getActiveSheet();
    $sheet->setTitle('Парсер');

    $hrefs = [];
    $numm = 1;
    $pages = 1;

    $hrefFrom = '<a class="fxtabl__td fxtabl__td_name" href="';
    $hrefTo = '">';

    // Сохраняем содержимое страниц со списком компаний

    for ($i = 1; $i <= $list_pages; $i++) {
        $browser = $browserFactory->createBrowser([
            'headless'        => $headless,
            'userAgent'       => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36',
            'windowSize'      => [100, 100],
        ]);

        $page = $browser->createPage();
        $uri = 'https://it-rating.in.ua/web-studiya?page=' . $i;
        
        $page->navigate($uri)->waitForNavigation(Page::NETWORK_IDLE);

        $html = $page->getHtml();
        $browser->close();

        // Выбераем имена компаний и href

        $parser = new Parser($html);
        $parserOfHrefs = new Parser($html);

        $parser->moveTo('<img width="31px" height="19px" class="fxtabl__img" src="image/theme-img/lider-icon/06.png" alt="alt6" title="title6">');
        $parserOfHrefs->moveTo('<div class="fxtablWrp categoryPage__fxtablWrp product-list">');

        for ($j = 0; $j < 20; $j++) {
            $sheet->setCellValue("A" . $numm, $parser->select('<span class="fxtabl__productName">', '</span>'));
            $href = $parserOfHrefs->select($hrefFrom, $hrefTo);

            // Сохроняем содержимое страниц с описанием отдельных компаний

            $browser = $browserFactory->createBrowser([
                'headless'        => $headless,
                'userAgent'       => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36',
                'windowSize'      => [100, 100],
            ]);

            $page = $browser->createPage();
            $page->navigate($href)->waitForNavigation(Page::NETWORK_IDLE);
    
            $html = $page->getHtml();
            $page->close();
            $browser->close();

            $companiesLeft = $list_pages * 20 - $numm;
            $pagesLeft = $list_pages - $pages;

            echo "\nCompany " . $numm . " finished (". $companiesLeft ." companies left) \n";
            echo "Page " . $pages . " processing (". $pagesLeft ." pages left) \n";
    
            $parserEmail = new Parser($html);
            $parserSite = new Parser($html);
    
            $parserSite->moveTo('<div class="companyCard__body">');
            $parserSite->moveTo('<a class="companyCard__site"');
            
            $parserEmail->moveTo('<div class="companyCard__body">');
            $parserEmail->moveTo('<a class="arrayInf__value arrayInf__value_email"');
    
            $sheet->setCellValue("B".$numm, trim($parserEmail->select('>', '</a>')));
            $sheet->setCellValue("C".$numm, trim($parserSite->select('target="_blank" rel="nofollow">', '</a>')));
    
            $numm++;
        }
        $pages++;

        // Записываем в таблицу имейлы
        $objWriter = new PHPExcel_Writer_Excel2007($xls);
        $objWriter->save(__DIR__ . '/' . $filename . '.xlsx'); 
    }

    
    
    