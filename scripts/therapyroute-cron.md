# TherapyRoute Scraper Scheduling

This guide explains how to run the PHP CLI scraper periodically without overloading the source website.

## Command

Use the CLI script with filters and a respectful delay:

```
php E:\xammp\htdocs\consultation_site\scraper\therapyroute_scraper.php --country=Uganda --pages=3 --delay=2-5 --resume
```

Adjust `country`, `city`, `pages`, and `delay` according to your needs.

## Windows (Task Scheduler)

1. Open Task Scheduler → Create Basic Task
2. Trigger: Daily or Weekly (choose time with low traffic)
3. Action: Start a Program
4. Program/script: `php`
5. Add arguments:
   ```
   E:\xammp\htdocs\consultation_site\scraper\therapyroute_scraper.php --country=Uganda --pages=3 --delay=2-5 --resume
   ```
6. Start in: `E:\xammp\htdocs\consultation_site`
7. Finish. Ensure the task runs under an account with access to MySQL.

## Linux/macOS (cron)

Edit crontab with `crontab -e` and add:

```
0 2 * * * /usr/bin/php /var/www/consultation_site/scraper/therapyroute_scraper.php --country=Uganda --pages=3 --delay=2-5 --resume >> /var/www/consultation_site/data/therapyroute_cron.log 2>&1
```

## Best Practices

- Respect `robots.txt` and site terms; use conservative `pages` and `delay`.
- Run at off-peak hours; stagger scrapes per region.
- Monitor `data/therapyroute_progress.json` and `scrape_runs` table for stats.
- Implement email alerting on failures (optional).