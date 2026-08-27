# Category List Last 30 Days - Quick Reference

## File
`backend2/app/Http/Controllers/Api/v18/HomeAppController.php`

## The Change
Add 1 line to filter news from last 30 days:

```php
->where('n.created_at', '>=', now()->subDays(30))
```

## Where to Add It

After this existing line:
```php
->where('n.status', 'Active')
```

Add this new line:
```php
->where('n.created_at', '>=', now()->subDays(30))
```

## Locations (4 places)

| # | Line | Redis Key |
|---|------|-----------|
| 1 | 1674 | categoriesapp |
| 2 | 1903 | categoriesapp1 |
| 3 | 2145 | categoriesapptopmenu |
| 4 | 2267 | categoriesapp |

Apply same filter to all 4 locations.

## Result

✅ Only categories with news from last 30 days appear
✅ Older news ignored
❌ Categories with no recent news hidden

## Example

- News category: 50 total articles, 8 from last 30 days → ✅ Shows
- Sports category: 25 total articles, 0 from last 30 days → ❌ Hidden
- Tech category: 15 total articles, 12 from last 30 days → ✅ Shows

## Implementation Time
~2 minutes

---

## Reference Files
- `CATEGORY_30DAYS_COPYPASTE.md` - Full copy/paste code
- `CATEGORY_NEWS_LAST_30_DAYS.md` - Detailed explanation
