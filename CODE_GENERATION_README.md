# Code Generation System

This system automatically generates 30,000 unique codes for each area using your existing URL endpoint.

## Overview

The system will:
- Generate codes for all areas: Jinja, Mbale, Lira, Gulu, Arua, Fort Portal, Mbarara, Masaka, and Kampala
- Target 30,000 codes per area
- Generate codes in batches of 100 per API call
- Check counts after each batch to ensure accuracy
- Use cache to track progress and skip completed areas
- Run automatically via cron job daily at 2:00 AM

## Commands

### 1. Test the Setup
```bash
php artisan command:testCodeGeneration
```
This command will:
- Test database connectivity
- Show current code counts for each area
- Display how many codes are needed per area
- Verify the system is ready for generation

### 2. Generate Codes Manually
```bash
php artisan command:generateCodes
```
This command will:
- Process only areas that need codes (using cache)
- Generate codes in batches of 100
- Show progress for each batch
- Continue until each area reaches 30,000 codes
- Handle errors gracefully
- Skip areas that are already completed

### 3. Reset Cache (Force Regeneration)
```bash
php artisan command:generateCodes --reset
```
This command will:
- Clear cache for all areas
- Force the system to process all areas again
- Useful for testing or when you want to regenerate all codes

### 4. Check Cron Job Status
```bash
# Check if Laravel scheduler is running
php artisan schedule:list

# Run scheduled commands manually
php artisan schedule:run
```

## Cron Job Setup

The system is configured to run automatically via Laravel's scheduler. To ensure it works:

### 1. Add to Server Crontab
```bash
# Edit crontab
crontab -e

# Add this line to run Laravel scheduler every minute
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

### 2. Verify Scheduler is Working
```bash
# Check scheduled commands
php artisan schedule:list

# Test the scheduler
php artisan schedule:run
```

## How It Works

1. **Smart Area Selection**: The system only processes areas that need codes (using cache)
2. **Cache Tracking**: Each area's progress is tracked in cache (processing/completed)
3. **Batch Generation**: Codes are generated in batches of 100 via your existing URL endpoint
4. **Count Verification**: After each batch, the system checks the actual count in the database
5. **Progress Tracking**: Shows real-time progress and adjusts batch sizes as needed
6. **Error Handling**: Continues processing other areas if one fails
7. **Safety Limits**: Maximum 100 batches per area to prevent infinite loops
8. **Completion Marking**: Areas that reach 30,000 codes are marked as completed in cache

## Configuration

You can modify these settings in `app/Console/Commands/generateCodes.php`:

```php
protected $targetCount = 30000;    // Target codes per area
protected $batchSize = 100;        // Codes per API call
protected $delay = 15;             // Seconds between API calls
```

## Monitoring

### Check Current Status
```bash
php artisan command:testCodeGeneration
```

### View Logs
```bash
tail -f storage/logs/laravel.log
```

### Monitor Database
```sql
-- Check total codes per area
SELECT area, COUNT(*) as total_codes 
FROM codes 
GROUP BY area 
ORDER BY total_codes DESC;

-- Check codes by status
SELECT area, status, COUNT(*) as count 
FROM codes 
GROUP BY area, status;
```

## Troubleshooting

### Common Issues

1. **Database Connection Failed**
   - Check database credentials in `.env`
   - Verify database server is running

2. **API Calls Failing**
   - Check if your application is accessible
   - Verify the URL endpoint `/codes/generate/{area}/{count}` works
   - Check application logs for errors

3. **Cron Job Not Running**
   - Verify crontab entry is correct
   - Check server timezone settings
   - Ensure Laravel scheduler is working

4. **Memory Issues**
   - Increase PHP memory limit in `php.ini`
   - Consider reducing batch size if needed

### Debug Mode

To see detailed output, you can modify the command to run in verbose mode or add more logging.

## Performance Notes

- **Batch Size**: 100 codes per batch provides good balance between speed and reliability
- **Delay**: 15 seconds between calls prevents overwhelming your system
- **Total Time**: Generating 30,000 codes for all areas may take several hours
- **Database**: Ensure your database can handle the volume of inserts

## Security Considerations

- The system uses your existing authentication and validation
- API calls are made internally (not exposed to external users)
- Database queries are properly sanitized
- Error messages don't expose sensitive information

## Support

If you encounter issues:
1. Check the logs: `storage/logs/laravel.log`
2. Run the test command: `php artisan command:testCodeGeneration`
3. Verify your existing code generation endpoint works manually
4. Check database connectivity and permissions
