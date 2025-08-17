<?php

declare(strict_types=1);

namespace StafeGroup\LaravelAppDeveloper\Mcp\Tools;

use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;

class CompareFeatures extends Tool
{
    public function description(): string
    {
        return 'Compares your Laravel application features with market leaders and competitors. This tool takes your application analysis and market research data to identify feature gaps, competitive advantages, and opportunities for improvement.';
    }

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->string('category')
            ->description('Application category for comparison (e.g., CRM, e-commerce, project management)')
            ->required()
            ->raw('current_features', [
                'description' => 'Current features of your application (from analyze-application tool)',
                'type' => 'array',
                'items' => ['type' => 'string'],
            ])
            ->optional()
            ->raw('market_leaders_data', [
                'description' => 'Market research data (from research-market-leaders tool)',
                'type' => 'object',
            ])
            ->optional()
            ->string('comparison_focus')
            ->description('Focus area for comparison: features, technology, user_experience, pricing, or comprehensive')
            ->optional()
            ->integer('priority_threshold')
            ->description('Minimum percentage of competitors that must have a feature to consider it high priority (default: 70)')
            ->optional();
    }

    /**
     * @param array<string, mixed> $arguments
     */
    public function handle(array $arguments): ToolResult
    {
        $category = $arguments['category'];
        $currentFeatures = $arguments['current_features'] ?? [];
        $marketLeadersData = $arguments['market_leaders_data'] ?? null;
        $comparisonFocus = $arguments['comparison_focus'] ?? 'comprehensive';
        $priorityThreshold = $arguments['priority_threshold'] ?? 70;

        try {
            // If market leaders data is not provided, get default data for the category
            if (!$marketLeadersData) {
                $marketLeadersData = $this->getDefaultMarketData($category);
            }

            $comparison = $this->performComparison(
                $currentFeatures, 
                $marketLeadersData, 
                $comparisonFocus,
                $priorityThreshold
            );
            
            return ToolResult::json([
                'comparison_summary' => [
                    'category' => $category,
                    'focus_area' => $comparisonFocus,
                    'priority_threshold' => $priorityThreshold,
                    'comparison_timestamp' => now()->toISOString(),
                ],
                'feature_comparison' => $comparison,
                'gap_analysis' => $this->performGapAnalysis($comparison, $priorityThreshold),
                'competitive_positioning' => $this->analyzeCompetitivePosition($comparison),
                'action_plan' => $this->generateActionPlan($comparison, $priorityThreshold),
            ]);
        } catch (\Exception $e) {
            return ToolResult::error('Failed to compare features: '.$e->getMessage());
        }
    }

    /**
     * Perform comprehensive feature comparison.
     */
    protected function performComparison(array $currentFeatures, ?array $marketData, string $focus, int $priorityThreshold): array
    {
        $comparison = [
            'features_present' => [],
            'features_missing' => [],
            'unique_features' => [],
            'market_coverage' => [],
            'technology_comparison' => [],
        ];

        if (!$marketData || !isset($marketData['market_leaders'])) {
            return $comparison;
        }

        $marketLeaders = $marketData['market_leaders'];
        $marketAnalysis = $marketData['market_analysis'] ?? [];

        // Extract all competitor features
        $competitorFeatures = [];
        foreach ($marketLeaders as $leader) {
            if (isset($leader['key_features'])) {
                foreach ($leader['key_features'] as $feature) {
                    $competitorFeatures[] = $feature;
                }
            }
        }

        // Count feature frequency
        $featureFrequency = array_count_values($competitorFeatures);
        $totalCompetitors = count($marketLeaders);

        // Normalize current features for comparison
        $normalizedCurrentFeatures = $this->normalizeFeatures($currentFeatures);
        $normalizedMarketFeatures = array_keys($featureFrequency);

        // Analyze feature presence
        foreach ($normalizedMarketFeatures as $marketFeature) {
            $frequency = $featureFrequency[$marketFeature];
            $percentage = ($frequency / $totalCompetitors) * 100;

            $featureData = [
                'feature' => $marketFeature,
                'market_adoption' => $percentage,
                'competitor_count' => $frequency,
                'priority' => $this->calculatePriority($percentage, $priorityThreshold),
            ];

            if ($this->hasFeature($marketFeature, $normalizedCurrentFeatures)) {
                $comparison['features_present'][] = $featureData;
            } else {
                $comparison['features_missing'][] = $featureData;
            }

            $comparison['market_coverage'][] = $featureData;
        }

        // Find unique features (present in your app but not in market leaders)
        foreach ($normalizedCurrentFeatures as $currentFeature) {
            if (!$this->hasFeature($currentFeature, $normalizedMarketFeatures)) {
                $comparison['unique_features'][] = [
                    'feature' => $currentFeature,
                    'competitive_advantage' => true,
                    'market_gap' => true,
                ];
            }
        }

        // Technology comparison
        if ($focus === 'technology' || $focus === 'comprehensive') {
            $comparison['technology_comparison'] = $this->compareTechnology($marketAnalysis);
        }

        // Sort arrays by priority/adoption
        usort($comparison['features_missing'], fn($a, $b) => $b['market_adoption'] <=> $a['market_adoption']);
        usort($comparison['features_present'], fn($a, $b) => $b['market_adoption'] <=> $a['market_adoption']);
        usort($comparison['market_coverage'], fn($a, $b) => $b['market_adoption'] <=> $a['market_adoption']);

        return $comparison;
    }

    /**
     * Perform gap analysis to identify critical missing features.
     */
    protected function performGapAnalysis(array $comparison, int $priorityThreshold): array
    {
        $gapAnalysis = [
            'critical_gaps' => [],
            'opportunity_gaps' => [],
            'nice_to_have_gaps' => [],
            'gap_score' => 0,
            'market_readiness' => 'unknown',
        ];

        foreach ($comparison['features_missing'] as $missingFeature) {
            $adoption = $missingFeature['market_adoption'];
            
            if ($adoption >= $priorityThreshold) {
                $gapAnalysis['critical_gaps'][] = $missingFeature;
            } elseif ($adoption >= 40) {
                $gapAnalysis['opportunity_gaps'][] = $missingFeature;
            } else {
                $gapAnalysis['nice_to_have_gaps'][] = $missingFeature;
            }
        }

        // Calculate gap score (lower is better)
        $totalFeatures = count($comparison['market_coverage']);
        $criticalGaps = count($gapAnalysis['critical_gaps']);
        $opportunityGaps = count($gapAnalysis['opportunity_gaps']);
        
        if ($totalFeatures > 0) {
            $gapAnalysis['gap_score'] = (($criticalGaps * 3) + ($opportunityGaps * 1)) / $totalFeatures * 100;
        }

        // Determine market readiness
        if ($gapAnalysis['gap_score'] <= 10) {
            $gapAnalysis['market_readiness'] = 'excellent';
        } elseif ($gapAnalysis['gap_score'] <= 25) {
            $gapAnalysis['market_readiness'] = 'good';
        } elseif ($gapAnalysis['gap_score'] <= 50) {
            $gapAnalysis['market_readiness'] = 'fair';
        } else {
            $gapAnalysis['market_readiness'] = 'needs_improvement';
        }

        return $gapAnalysis;
    }

    /**
     * Analyze competitive positioning.
     */
    protected function analyzeCompetitivePosition(array $comparison): array
    {
        $positioning = [
            'strength_areas' => [],
            'weakness_areas' => [],
            'differentiation_opportunities' => [],
            'competitive_score' => 0,
        ];

        $totalMarketFeatures = count($comparison['market_coverage']);
        $presentFeatures = count($comparison['features_present']);
        $uniqueFeatures = count($comparison['unique_features']);

        // Calculate competitive score
        if ($totalMarketFeatures > 0) {
            $marketCoverage = ($presentFeatures / $totalMarketFeatures) * 100;
            $innovationBonus = min($uniqueFeatures * 5, 20); // Max 20% bonus for innovation
            $positioning['competitive_score'] = min($marketCoverage + $innovationBonus, 100);
        }

        // Identify strength areas (high-adoption features you have)
        foreach ($comparison['features_present'] as $feature) {
            if ($feature['market_adoption'] >= 70) {
                $positioning['strength_areas'][] = $feature['feature'];
            }
        }

        // Identify weakness areas (high-adoption features you're missing)
        foreach ($comparison['features_missing'] as $feature) {
            if ($feature['market_adoption'] >= 70) {
                $positioning['weakness_areas'][] = $feature['feature'];
            }
        }

        // Differentiation opportunities (your unique features)
        foreach ($comparison['unique_features'] as $feature) {
            $positioning['differentiation_opportunities'][] = $feature['feature'];
        }

        return $positioning;
    }

    /**
     * Generate actionable improvement plan.
     */
    protected function generateActionPlan(array $comparison, int $priorityThreshold): array
    {
        $actionPlan = [
            'immediate_actions' => [],
            'short_term_goals' => [],
            'long_term_strategy' => [],
            'estimated_development_time' => [],
        ];

        // Immediate actions (critical missing features)
        foreach ($comparison['features_missing'] as $feature) {
            if ($feature['market_adoption'] >= $priorityThreshold) {
                $action = [
                    'action' => "Implement {$feature['feature']}",
                    'rationale' => "Present in {$feature['competitor_count']} competitors ({$feature['market_adoption']}% market adoption)",
                    'priority' => 'high',
                    'estimated_effort' => $this->estimateEffort($feature['feature']),
                ];
                $actionPlan['immediate_actions'][] = $action;
            }
        }

        // Short-term goals (opportunity features)
        foreach ($comparison['features_missing'] as $feature) {
            if ($feature['market_adoption'] >= 40 && $feature['market_adoption'] < $priorityThreshold) {
                $action = [
                    'action' => "Consider implementing {$feature['feature']}",
                    'rationale' => "Growing market trend ({$feature['market_adoption']}% adoption)",
                    'priority' => 'medium',
                    'estimated_effort' => $this->estimateEffort($feature['feature']),
                ];
                $actionPlan['short_term_goals'][] = $action;
            }
        }

        // Long-term strategy
        $actionPlan['long_term_strategy'] = [
            'Leverage unique features as competitive advantages',
            'Monitor emerging trends in the market',
            'Focus on user experience improvements',
            'Consider API integrations for missing features',
        ];

        // Add development time estimates
        $totalEffort = 0;
        foreach ($actionPlan['immediate_actions'] as $action) {
            $totalEffort += $action['estimated_effort'];
        }
        foreach ($actionPlan['short_term_goals'] as $action) {
            $totalEffort += $action['estimated_effort'];
        }

        $actionPlan['estimated_development_time'] = [
            'immediate_features' => array_sum(array_column($actionPlan['immediate_actions'], 'estimated_effort')),
            'short_term_features' => array_sum(array_column($actionPlan['short_term_goals'], 'estimated_effort')),
            'total_estimated_weeks' => $totalEffort,
        ];

        return $actionPlan;
    }

    /**
     * Get default market data for a category.
     */
    protected function getDefaultMarketData(string $category): array
    {
        // This would ideally call the ResearchMarketLeaders tool, but for now return basic structure
        return [
            'category' => $category,
            'market_leaders' => [],
            'market_analysis' => [
                'total_companies' => 0,
                'common_features' => [],
                'technology_trends' => [],
            ],
        ];
    }

    /**
     * Normalize features for comparison.
     */
    protected function normalizeFeatures(array $features): array
    {
        return array_map(function($feature) {
            return strtolower(trim(str_replace(['_', '-'], ' ', $feature)));
        }, $features);
    }

    /**
     * Check if a feature is present in the feature list.
     */
    protected function hasFeature(string $targetFeature, array $featureList): bool
    {
        $normalizedTarget = strtolower(trim(str_replace(['_', '-'], ' ', $targetFeature)));
        
        foreach ($featureList as $feature) {
            $normalizedFeature = strtolower(trim(str_replace(['_', '-'], ' ', $feature)));
            
            // Exact match
            if ($normalizedFeature === $normalizedTarget) {
                return true;
            }
            
            // Partial match (contains)
            if (str_contains($normalizedFeature, $normalizedTarget) || str_contains($normalizedTarget, $normalizedFeature)) {
                // Additional similarity check for better accuracy
                $similarity = 0;
                similar_text($normalizedFeature, $normalizedTarget, $similarity);
                if ($similarity > 80) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Calculate feature priority based on market adoption.
     */
    protected function calculatePriority(float $adoption, int $threshold): string
    {
        if ($adoption >= $threshold) {
            return 'high';
        } elseif ($adoption >= 40) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    /**
     * Compare technology stacks.
     */
    protected function compareTechnology(array $marketAnalysis): array
    {
        $techComparison = [
            'trending_technologies' => [],
            'recommendations' => [],
        ];

        if (isset($marketAnalysis['technology_trends'])) {
            $techComparison['trending_technologies'] = $marketAnalysis['technology_trends'];
            
            // Generate tech recommendations
            foreach ($marketAnalysis['technology_trends'] as $tech => $count) {
                if ($count >= 3) { // If 3+ competitors use it
                    $techComparison['recommendations'][] = "Consider adopting {$tech} (used by {$count} competitors)";
                }
            }
        }

        return $techComparison;
    }

    /**
     * Estimate development effort for a feature.
     */
    protected function estimateEffort(string $feature): int
    {
        // Simple effort estimation in weeks
        $complexFeatures = ['ai', 'machine learning', 'analytics', 'reporting', 'automation', 'integration'];
        $mediumFeatures = ['user management', 'authentication', 'notifications', 'search', 'dashboard'];
        
        $feature = strtolower($feature);
        
        foreach ($complexFeatures as $complex) {
            if (str_contains($feature, $complex)) {
                return rand(4, 8); // 4-8 weeks
            }
        }
        
        foreach ($mediumFeatures as $medium) {
            if (str_contains($feature, $medium)) {
                return rand(2, 4); // 2-4 weeks
            }
        }
        
        return rand(1, 3); // 1-3 weeks for simple features
    }
}