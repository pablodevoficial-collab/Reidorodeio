<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class QuickScoringController extends Controller
{
    public function index()
    {
        $pageTitle = 'Pontuação Rápida';
        return view('admin.quick_scoring.index', compact('pageTitle'));
    }

    public function getCompetitors(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Quick Scoring ainda não está implementado neste build.'
        ], 200);
    }

    public function applyScore(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Quick Scoring ainda não está implementado neste build.'
        ], 200);
    }

    public function applyCustomScore(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Quick Scoring ainda não está implementado neste build.'
        ], 200);
    }

    public function undoScore(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Quick Scoring ainda não está implementado neste build.'
        ], 200);
    }

    public function disconnectWebSockets(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Quick Scoring ainda não está implementado neste build.'
        ], 200);
    }

    public function checkForUpdates(Request $request)
    {
        return response()->json([
            'success' => true,
            'has_updates' => false,
            'message' => 'Sem atualizações pendentes.'
        ]);
    }

    public function getModalidadeStats(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Quick Scoring ainda não está implementado neste build.'
        ], 200);
    }

    public function getRanking(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Quick Scoring ainda não está implementado neste build.'
        ], 200);
    }

    public function exportResults(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Quick Scoring ainda não está implementado neste build.'
        ], 200);
    }
}
