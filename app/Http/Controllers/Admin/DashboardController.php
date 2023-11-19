<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    //
    public function index(Request $rquest)
    {
        $categoriesCount = Category::count();
        $articlesCount = Article::count();

        $latestCategory = Category::latest('created_at')->value('created_at') ? Category::latest('created_at')->value('created_at')->diffForHumans() : 'No records available';
        $latestArticle = Article::latest('created_at')->value('created_at') ? Article::latest('created_at')->value('created_at')->diffForHumans() : 'No records available';

        return Inertia::render('Dashboard', [
            'categoriesCount' => $categoriesCount,
            'articlesCount' => $articlesCount,
            'latestCategory' => $latestCategory,
            'latestArticle' => $latestArticle,
        ]);
    }
}
