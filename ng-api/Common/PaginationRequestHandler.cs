namespace api_v2.Common;

public class PaginationRequestHandler(IQueryCollection query, int totalCount)
{
    public int GetResultsPerPage()
    {
        return query.TryGetValue("resultsPerPage", out var resultsPerPage) ? int.Parse(resultsPerPage) : 20;
    }

    public int CalculateOffset()
    {
        return (CalculatePageCount() - 1) * GetResultsPerPage();
    }

    public int CalculatePageCount()
    {
        return Math.Max(1, (int)Math.Ceiling((double)(totalCount / GetResultsPerPage())));
    }
}
