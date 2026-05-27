<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MetaAdsInsight;

class MetaAdsInsightsController extends Controller
{
    public function index(Request $request)
    {
        $query = MetaAdsInsight::query();

        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }

        if ($request->filled('campaign_id')) {
            $query->where('campaign_id', $request->campaign_id);
        }

        if ($request->filled('date_start')) {
            $query->where('date_start', $request->date_start);
        }

        return response()->json($query->orderBy('date_start', 'desc')->paginate(20));
    }

    public function show($id)
    {
        return response()->json(MetaAdsInsight::findOrFail($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'account_name' => 'nullable|string',
            'account_id' => 'required|string|max:30',
            'campaign_id' => 'nullable|string|max:30',
            'campaign_name' => 'nullable|string',
            'date_start' => 'required|date',
            'spend' => 'nullable|numeric',
            'clicks' => 'nullable|integer',
            'impressions' => 'nullable|integer',
            'reach' => 'nullable|integer',
            'ctr' => 'nullable|numeric',
            'cpm' => 'nullable|numeric',
            'cpc' => 'nullable|numeric',
            'link_clicks' => 'nullable|integer',
            'leads' => 'nullable|integer',
            'purchases' => 'nullable|integer',
            'synced_at' => 'nullable|date',
        ]);

        $insight = MetaAdsInsight::create($data);

        return response()->json($insight, 201);
    }

    public function update(Request $request, $id)
    {
        $insight = MetaAdsInsight::findOrFail($id);

        $data = $request->validate([
            'account_name' => 'nullable|string',
            'account_id' => 'nullable|string|max:30',
            'campaign_id' => 'nullable|string|max:30',
            'campaign_name' => 'nullable|string',
            'date_start' => 'nullable|date',
            'spend' => 'nullable|numeric',
            'clicks' => 'nullable|integer',
            'impressions' => 'nullable|integer',
            'reach' => 'nullable|integer',
            'ctr' => 'nullable|numeric',
            'cpm' => 'nullable|numeric',
            'cpc' => 'nullable|numeric',
            'link_clicks' => 'nullable|integer',
            'leads' => 'nullable|integer',
            'purchases' => 'nullable|integer',
            'synced_at' => 'nullable|date',
        ]);

        $insight->update($data);

        return response()->json($insight);
    }

    public function destroy($id)
    {
        MetaAdsInsight::findOrFail($id)->delete();

        return response()->json(null, 204);
    }
}
