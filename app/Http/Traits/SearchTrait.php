<?php


namespace App\Http\Traits;


use App\Models\SearchHistory;
use App\Models\SearchTop;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

trait SearchTrait
{
    /**
     * create history search
     * @param string $keyword
     * @param int $userID
     * @author QuangNh
     */
    public function createHistorySearch(string $keyword, int $userID)
    {
        $searchHistory = SearchHistory::create([
            'user_id' => $userID,
            'keyword' => $keyword,
            'created_at' => strtotime(Carbon::now()),
        ]);

        if ($searchHistory) {
            $this->updateOrCreateTopSearch($keyword);
        }

        Log::debug('Tạo lịch sử tìm kiếm: '. $searchHistory);
    }

    /**
     * create top search
     * @param string $keyword
     * @author QuangNh
     */
    public function updateOrCreateTopSearch(string $keyword)
    {
        $searchTop = SearchTop::firstOrNew(['keyword' => $keyword]);

        $searchTop->count++;

        $updateOrCreateTopSearch = $searchTop->save();

        Log::debug('Thêm mới hoặc tăng lượt tìm kiếm bởi từ khóa: '. $updateOrCreateTopSearch);
    }

    /**
     * get data search
     * @param int $userID
     * @return array $search
     * @author QuangNh
     */
    public function getDataSearch(int $userID): array
    {
        $result['historySearch'] = SearchHistory::where('user_id', $userID)
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        $result['topSearch'] = SearchTop::orderBy('count', 'DESC')
            ->take(3)
            ->get();

        return $result;
    }

    /**
     * del data search
     * @param int $userID
     * @param string $keyword
     * @return boolean
     * @author QuangNh
     */
    public function delDataSearch(int $userID, string $keyword): bool
    {
        return SearchHistory::where('user_id', $userID)
            ->where('keyword', $keyword)
            ->delete();
    }
}
