<?php

declare(strict_types=1);

namespace StafeGroup\LaravelAppDeveloper\Mcp\Tools;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;

class ResearchMarketLeaders extends Tool
{
    public function description(): string
    {
        return 'Researches and analyzes market leaders and top competitors in a specified application category. This tool searches for the most popular and successful applications in categories like CRM, e-commerce, project management, social media, etc., and provides detailed feature analysis and market insights.';
    }

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->string('category')
            ->description('Application category to research (e.g., CRM, e-commerce, project management, social media, analytics, healthcare, education, fintech)')
            ->required()
            ->integer('limit')
            ->description('Maximum number of competitors to research (default: 10, max: 20)')
            ->optional()
            ->string('focus_area')
            ->description('Specific focus area: features, pricing, technology, user_experience, or all')
            ->optional()
            ->boolean('include_startups')
            ->description('Include emerging startups and newer companies (default: false)')
            ->optional()
            ->string('market_segment')
            ->description('Market segment: enterprise, sme, consumer, or all (default: all)')
            ->optional();
    }

    /**
     * @param array<string, mixed> $arguments
     */
    public function handle(array $arguments): ToolResult
    {
        $category = $arguments['category'];
        $limit = min($arguments['limit'] ?? 10, 20);
        $focusArea = $arguments['focus_area'] ?? 'all';
        $includeStartups = $arguments['include_startups'] ?? false;
        $marketSegment = $arguments['market_segment'] ?? 'all';

        try {
            $marketLeaders = $this->researchCategory($category, $limit, $includeStartups, $marketSegment);
            $analysis = $this->analyzeMarketLeaders($marketLeaders, $focusArea);
            
            return ToolResult::json([
                'category' => $category,
                'research_parameters' => [
                    'limit' => $limit,
                    'focus_area' => $focusArea,
                    'include_startups' => $includeStartups,
                    'market_segment' => $marketSegment,
                    'research_timestamp' => now()->toISOString(),
                ],
                'market_leaders' => $marketLeaders,
                'market_analysis' => $analysis,
                'recommendations' => $this->generateRecommendations($marketLeaders, $analysis),
            ]);
        } catch (\Exception $e) {
            return ToolResult::error('Failed to research market leaders: '.$e->getMessage());
        }
    }

    /**
     * Research top companies in the specified category.
     */
    protected function researchCategory(string $category, int $limit, bool $includeStartups, string $marketSegment): array
    {
        // This would ideally use real APIs, but for the MVP we'll use curated data
        $marketLeadersDatabase = $this->getMarketLeadersDatabase();
        
        $categoryLeaders = $marketLeadersDatabase[$category] ?? [];
        
        if (empty($categoryLeaders)) {
            // Try to find similar categories
            $similarCategories = $this->findSimilarCategories($category, $marketLeadersDatabase);
            if (!empty($similarCategories)) {
                $categoryLeaders = $marketLeadersDatabase[$similarCategories[0]] ?? [];
            }
        }

        // Filter by market segment
        if ($marketSegment !== 'all') {
            $categoryLeaders = array_filter($categoryLeaders, function($leader) use ($marketSegment) {
                return in_array($marketSegment, $leader['market_segments'] ?? ['all']);
            });
        }

        // Filter startups if not included
        if (!$includeStartups) {
            $categoryLeaders = array_filter($categoryLeaders, function($leader) {
                return ($leader['founded_year'] ?? 2025) < 2020;
            });
        }

        // Limit results
        return array_slice($categoryLeaders, 0, $limit);
    }

    /**
     * Analyze the researched market leaders.
     */
    protected function analyzeMarketLeaders(array $marketLeaders, string $focusArea): array
    {
        $analysis = [
            'total_companies' => count($marketLeaders),
            'average_valuation' => 0,
            'common_features' => [],
            'pricing_models' => [],
            'technology_trends' => [],
            'market_insights' => [],
        ];

        if (empty($marketLeaders)) {
            return $analysis;
        }

        // Calculate average valuation
        $valuations = array_filter(array_column($marketLeaders, 'valuation'));
        if (!empty($valuations)) {
            $analysis['average_valuation'] = array_sum($valuations) / count($valuations);
        }

        // Analyze common features
        $allFeatures = [];
        foreach ($marketLeaders as $leader) {
            if (isset($leader['key_features'])) {
                $allFeatures = array_merge($allFeatures, $leader['key_features']);
            }
        }
        
        $featureCounts = array_count_values($allFeatures);
        arsort($featureCounts);
        
        $analysis['common_features'] = array_slice($featureCounts, 0, 10, true);

        // Analyze pricing models
        $pricingModels = array_column($marketLeaders, 'pricing_model');
        $analysis['pricing_models'] = array_count_values(array_filter($pricingModels));

        // Technology trends
        $technologies = [];
        foreach ($marketLeaders as $leader) {
            if (isset($leader['technologies'])) {
                $technologies = array_merge($technologies, $leader['technologies']);
            }
        }
        $techCounts = array_count_values($technologies);
        arsort($techCounts);
        $analysis['technology_trends'] = array_slice($techCounts, 0, 8, true);

        // Market insights
        $analysis['market_insights'] = [
            'most_funded_company' => $this->getMostFundedCompany($marketLeaders),
            'newest_company' => $this->getNewestCompany($marketLeaders),
            'largest_user_base' => $this->getLargestUserBase($marketLeaders),
            'dominant_pricing_model' => array_key_first($analysis['pricing_models']),
        ];

        return $analysis;
    }

    /**
     * Generate recommendations based on market research.
     */
    protected function generateRecommendations(array $marketLeaders, array $analysis): array
    {
        $recommendations = [
            'must_have_features' => [],
            'competitive_advantages' => [],
            'market_opportunities' => [],
            'pricing_strategy' => '',
            'technology_stack' => [],
        ];

        // Must-have features (features present in 70%+ of leaders)
        $totalCompanies = count($marketLeaders);
        foreach ($analysis['common_features'] as $feature => $count) {
            if ($count >= ($totalCompanies * 0.7)) {
                $recommendations['must_have_features'][] = $feature;
            }
        }

        // Competitive advantages (features present in 30-60% of leaders)
        foreach ($analysis['common_features'] as $feature => $count) {
            $percentage = $count / $totalCompanies;
            if ($percentage >= 0.3 && $percentage < 0.7) {
                $recommendations['competitive_advantages'][] = $feature;
            }
        }

        // Market opportunities (underrepresented features)
        $recommendations['market_opportunities'] = [
            'Identify features present in less than 30% of market leaders',
            'Focus on underserved customer segments',
            'Explore emerging technology adoption',
            'Consider mobile-first or AI-enhanced features',
        ];

        // Pricing strategy
        if (!empty($analysis['pricing_models'])) {
            $dominantModel = array_key_first($analysis['pricing_models']);
            $recommendations['pricing_strategy'] = "Consider {$dominantModel} pricing model (used by most competitors)";
        }

        // Technology stack recommendations
        $recommendations['technology_stack'] = array_keys(array_slice($analysis['technology_trends'], 0, 5, true));

        return $recommendations;
    }

    /**
     * Get curated database of market leaders by category.
     */
    protected function getMarketLeadersDatabase(): array
    {
        return [
            'crm' => [
                [
                    'name' => 'Salesforce',
                    'website' => 'salesforce.com',
                    'valuation' => 250000000000,
                    'founded_year' => 1999,
                    'employees' => 73000,
                    'users' => 150000,
                    'market_segments' => ['enterprise', 'sme'],
                    'pricing_model' => 'subscription',
                    'key_features' => ['lead management', 'contact management', 'sales automation', 'reporting', 'email integration', 'mobile app', 'customization', 'integrations', 'analytics', 'workflow automation'],
                    'technologies' => ['cloud', 'mobile', 'ai', 'api', 'saas'],
                ],
                [
                    'name' => 'HubSpot',
                    'website' => 'hubspot.com',
                    'valuation' => 20000000000,
                    'founded_year' => 2006,
                    'employees' => 7000,
                    'users' => 120000,
                    'market_segments' => ['sme', 'enterprise'],
                    'pricing_model' => 'freemium',
                    'key_features' => ['lead management', 'contact management', 'email marketing', 'content management', 'social media', 'analytics', 'landing pages', 'forms', 'workflows', 'reporting'],
                    'technologies' => ['cloud', 'mobile', 'ai', 'api', 'saas'],
                ],
                [
                    'name' => 'Pipedrive',
                    'website' => 'pipedrive.com',
                    'valuation' => 1500000000,
                    'founded_year' => 2010,
                    'employees' => 850,
                    'users' => 100000,
                    'market_segments' => ['sme'],
                    'pricing_model' => 'subscription',
                    'key_features' => ['pipeline management', 'contact management', 'email sync', 'mobile app', 'reporting', 'automation', 'integrations', 'customization'],
                    'technologies' => ['cloud', 'mobile', 'api', 'saas'],
                ],
                [
                    'name' => 'Monday.com',
                    'website' => 'monday.com',
                    'valuation' => 7600000000,
                    'founded_year' => 2012,
                    'employees' => 1500,
                    'users' => 180000,
                    'market_segments' => ['sme', 'enterprise'],
                    'pricing_model' => 'subscription',
                    'key_features' => ['project management', 'team collaboration', 'automation', 'dashboards', 'time tracking', 'templates', 'integrations', 'mobile app'],
                    'technologies' => ['cloud', 'mobile', 'ai', 'api', 'saas'],
                ],
                [
                    'name' => 'Zoho CRM',
                    'website' => 'zoho.com',
                    'valuation' => 1000000000,
                    'founded_year' => 1996,
                    'employees' => 12000,
                    'users' => 250000,
                    'market_segments' => ['sme', 'enterprise'],
                    'pricing_model' => 'subscription',
                    'key_features' => ['lead management', 'contact management', 'sales automation', 'email integration', 'analytics', 'customization', 'mobile app', 'social media integration'],
                    'technologies' => ['cloud', 'mobile', 'ai', 'api', 'saas'],
                ],
            ],
            'e-commerce' => [
                [
                    'name' => 'Shopify',
                    'website' => 'shopify.com',
                    'valuation' => 65000000000,
                    'founded_year' => 2006,
                    'employees' => 10000,
                    'users' => 1700000,
                    'market_segments' => ['sme', 'enterprise'],
                    'pricing_model' => 'subscription',
                    'key_features' => ['online store builder', 'payment processing', 'inventory management', 'shipping', 'marketing tools', 'analytics', 'mobile app', 'themes', 'app store', 'multi-channel'],
                    'technologies' => ['cloud', 'mobile', 'api', 'saas', 'pwa'],
                ],
                [
                    'name' => 'WooCommerce',
                    'website' => 'woocommerce.com',
                    'valuation' => 1000000000,
                    'founded_year' => 2011,
                    'employees' => 200,
                    'users' => 5000000,
                    'market_segments' => ['sme'],
                    'pricing_model' => 'open source',
                    'key_features' => ['wordpress integration', 'customization', 'payment processing', 'inventory management', 'shipping', 'extensions', 'themes', 'analytics'],
                    'technologies' => ['php', 'wordpress', 'mysql', 'api'],
                ],
                [
                    'name' => 'Magento',
                    'website' => 'magento.com',
                    'valuation' => 1680000000,
                    'founded_year' => 2008,
                    'employees' => 1000,
                    'users' => 300000,
                    'market_segments' => ['enterprise', 'sme'],
                    'pricing_model' => 'open source',
                    'key_features' => ['b2b commerce', 'b2c commerce', 'multi-store', 'customization', 'inventory management', 'payment processing', 'shipping', 'analytics'],
                    'technologies' => ['php', 'mysql', 'elasticsearch', 'api', 'cloud'],
                ],
                [
                    'name' => 'BigCommerce',
                    'website' => 'bigcommerce.com',
                    'valuation' => 1500000000,
                    'founded_year' => 2009,
                    'employees' => 1000,
                    'users' => 60000,
                    'market_segments' => ['sme', 'enterprise'],
                    'pricing_model' => 'subscription',
                    'key_features' => ['online store', 'payment processing', 'inventory management', 'multi-channel', 'api-first', 'themes', 'apps', 'analytics'],
                    'technologies' => ['cloud', 'api', 'saas', 'headless'],
                ],
                [
                    'name' => 'Square',
                    'website' => 'squareup.com',
                    'valuation' => 29000000000,
                    'founded_year' => 2009,
                    'employees' => 8000,
                    'users' => 4000000,
                    'market_segments' => ['sme'],
                    'pricing_model' => 'transaction-based',
                    'key_features' => ['pos system', 'online store', 'payment processing', 'inventory management', 'analytics', 'loyalty programs', 'marketing', 'mobile app'],
                    'technologies' => ['cloud', 'mobile', 'api', 'saas'],
                ],
            ],
            'project management' => [
                [
                    'name' => 'Asana',
                    'website' => 'asana.com',
                    'valuation' => 5500000000,
                    'founded_year' => 2008,
                    'employees' => 2000,
                    'users' => 119000,
                    'market_segments' => ['sme', 'enterprise'],
                    'pricing_model' => 'freemium',
                    'key_features' => ['task management', 'project tracking', 'team collaboration', 'timelines', 'dashboards', 'automation', 'templates', 'integrations', 'mobile app'],
                    'technologies' => ['cloud', 'mobile', 'ai', 'api', 'saas'],
                ],
                [
                    'name' => 'Trello',
                    'website' => 'trello.com',
                    'valuation' => 425000000,
                    'founded_year' => 2011,
                    'employees' => 100,
                    'users' => 50000000,
                    'market_segments' => ['sme', 'consumer'],
                    'pricing_model' => 'freemium',
                    'key_features' => ['kanban boards', 'task management', 'team collaboration', 'automation', 'templates', 'integrations', 'mobile app'],
                    'technologies' => ['cloud', 'mobile', 'api', 'saas'],
                ],
                [
                    'name' => 'Jira',
                    'website' => 'atlassian.com',
                    'valuation' => 60000000000,
                    'founded_year' => 2002,
                    'employees' => 8000,
                    'users' => 180000,
                    'market_segments' => ['enterprise', 'sme'],
                    'pricing_model' => 'subscription',
                    'key_features' => ['issue tracking', 'agile planning', 'project management', 'reporting', 'automation', 'integrations', 'customization'],
                    'technologies' => ['cloud', 'mobile', 'api', 'saas'],
                ],
                [
                    'name' => 'Notion',
                    'website' => 'notion.so',
                    'valuation' => 10000000000,
                    'founded_year' => 2016,
                    'employees' => 500,
                    'users' => 30000000,
                    'market_segments' => ['sme', 'consumer'],
                    'pricing_model' => 'freemium',
                    'key_features' => ['workspace', 'note-taking', 'project management', 'collaboration', 'templates', 'databases', 'automation', 'integrations'],
                    'technologies' => ['cloud', 'mobile', 'ai', 'api', 'saas'],
                ],
                [
                    'name' => 'ClickUp',
                    'website' => 'clickup.com',
                    'valuation' => 4000000000,
                    'founded_year' => 2017,
                    'employees' => 800,
                    'users' => 10000000,
                    'market_segments' => ['sme', 'enterprise'],
                    'pricing_model' => 'freemium',
                    'key_features' => ['task management', 'project tracking', 'time tracking', 'docs', 'goals', 'automation', 'templates', 'integrations'],
                    'technologies' => ['cloud', 'mobile', 'ai', 'api', 'saas'],
                ],
            ],
            // Add more categories as needed
        ];
    }

    /**
     * Find similar categories if exact match not found.
     */
    protected function findSimilarCategories(string $category, array $database): array
    {
        $category = strtolower($category);
        $similar = [];
        
        foreach (array_keys($database) as $dbCategory) {
            if (str_contains($dbCategory, $category) || str_contains($category, $dbCategory)) {
                $similar[] = $dbCategory;
            }
        }
        
        return $similar;
    }

    /**
     * Get the most funded company.
     */
    protected function getMostFundedCompany(array $marketLeaders): ?array
    {
        $maxValuation = 0;
        $mostFunded = null;
        
        foreach ($marketLeaders as $leader) {
            if (($leader['valuation'] ?? 0) > $maxValuation) {
                $maxValuation = $leader['valuation'];
                $mostFunded = $leader;
            }
        }
        
        return $mostFunded;
    }

    /**
     * Get the newest company.
     */
    protected function getNewestCompany(array $marketLeaders): ?array
    {
        $latestYear = 0;
        $newest = null;
        
        foreach ($marketLeaders as $leader) {
            if (($leader['founded_year'] ?? 0) > $latestYear) {
                $latestYear = $leader['founded_year'];
                $newest = $leader;
            }
        }
        
        return $newest;
    }

    /**
     * Get the company with largest user base.
     */
    protected function getLargestUserBase(array $marketLeaders): ?array
    {
        $maxUsers = 0;
        $largest = null;
        
        foreach ($marketLeaders as $leader) {
            if (($leader['users'] ?? 0) > $maxUsers) {
                $maxUsers = $leader['users'];
                $largest = $leader;
            }
        }
        
        return $largest;
    }
}