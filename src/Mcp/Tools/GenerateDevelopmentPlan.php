<?php

declare(strict_types=1);

namespace StafeGroup\LaravelAppDeveloper\Mcp\Tools;

use Illuminate\Support\Facades\File;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;

class GenerateDevelopmentPlan extends Tool
{
    public function description(): string
    {
        return 'Generates comprehensive development plans in markdown format, optimized for AI assistants like Claude Code. Creates detailed DEVELOPMENT_PLAN.md files with structured tasks, timelines, and implementation steps that can be easily read and executed by AI developers.';
    }

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->string('project_name')
            ->description('Name of the project or application to be developed')
            ->required()
            ->string('project_type')
            ->description('Type of project: web_application, mobile_app, api, full_stack, microservice, or custom')
            ->required()
            ->string('description')
            ->description('Detailed description of what the application should do and its main purpose')
            ->required()
            ->raw('features', [
                'description' => 'List of features to include in the development plan',
                'type' => 'array',
                'items' => ['type' => 'string'],
            ])
            ->optional()
            ->string('tech_stack')
            ->description('Preferred technology stack (e.g., Laravel, Vue.js, React, etc.)')
            ->optional()
            ->string('timeline')
            ->description('Desired timeline: sprint, month, quarter, or custom duration')
            ->optional()
            ->string('complexity')
            ->description('Project complexity: simple, medium, complex, enterprise')
            ->optional()
            ->boolean('include_testing')
            ->description('Include testing strategies and test tasks')
            ->optional()
            ->boolean('include_deployment')
            ->description('Include deployment and DevOps tasks')
            ->optional()
            ->string('target_audience')
            ->description('Target audience or user type (e.g., enterprise, consumers, developers)')
            ->optional()
            ->string('output_file')
            ->description('Custom output filename (defaults to DEVELOPMENT_PLAN.md)')
            ->optional();
    }

    /**
     * @param array<string, mixed> $arguments
     */
    public function handle(array $arguments): ToolResult
    {
        $projectName = $arguments['project_name'];
        $projectType = $arguments['project_type'] ?? 'web_application';
        $description = $arguments['description'];
        $features = $arguments['features'] ?? [];
        $techStack = $arguments['tech_stack'] ?? 'Laravel, Vue.js, MySQL';
        $timeline = $arguments['timeline'] ?? 'quarter';
        $complexity = $arguments['complexity'] ?? 'medium';
        $includeTesting = $arguments['include_testing'] ?? true;
        $includeDeployment = $arguments['include_deployment'] ?? true;
        $targetAudience = $arguments['target_audience'] ?? 'general users';
        $outputFile = $arguments['output_file'] ?? 'DEVELOPMENT_PLAN.md';

        try {
            $plan = $this->generateComprehensivePlan([
                'project_name' => $projectName,
                'project_type' => $projectType,
                'description' => $description,
                'features' => $features,
                'tech_stack' => $techStack,
                'timeline' => $timeline,
                'complexity' => $complexity,
                'include_testing' => $includeTesting,
                'include_deployment' => $includeDeployment,
                'target_audience' => $targetAudience,
            ]);

            $markdownContent = $this->generateMarkdown($plan);
            $filePath = $this->savePlanToFile($markdownContent, $outputFile);

            return ToolResult::json([
                'success' => true,
                'file_path' => $filePath,
                'plan_summary' => [
                    'project_name' => $projectName,
                    'total_phases' => count($plan['phases']),
                    'total_tasks' => $this->countTotalTasks($plan),
                    'estimated_duration' => $plan['project_timeline']['total_duration'],
                    'complexity_level' => $complexity,
                ],
                'plan_structure' => $this->getPlanStructure($plan),
                'ai_instructions' => $this->generateAIInstructions($plan),
            ]);
        } catch (\Exception $e) {
            return ToolResult::error('Failed to generate development plan: '.$e->getMessage());
        }
    }

    /**
     * Generate a comprehensive development plan.
     */
    protected function generateComprehensivePlan(array $params): array
    {
        $plan = [
            'metadata' => $this->generateMetadata($params),
            'project_overview' => $this->generateProjectOverview($params),
            'requirements' => $this->generateRequirements($params),
            'architecture' => $this->generateArchitecture($params),
            'tech_stack' => $this->generateTechStackDetails($params['tech_stack']),
            'phases' => $this->generatePhases($params),
            'tasks' => $this->generateDetailedTasks($params),
            'testing_strategy' => $params['include_testing'] ? $this->generateTestingStrategy($params) : null,
            'deployment_strategy' => $params['include_deployment'] ? $this->generateDeploymentStrategy($params) : null,
            'project_timeline' => $this->generateTimeline($params),
            'team_structure' => $this->generateTeamStructure($params),
            'risk_analysis' => $this->generateRiskAnalysis($params),
            'success_metrics' => $this->generateSuccessMetrics($params),
        ];

        return array_filter($plan); // Remove null values
    }

    /**
     * Generate project metadata.
     */
    protected function generateMetadata(array $params): array
    {
        return [
            'project_name' => $params['project_name'],
            'project_type' => $params['project_type'],
            'complexity' => $params['complexity'],
            'target_audience' => $params['target_audience'],
            'created_at' => now()->toISOString(),
            'version' => '1.0',
            'ai_optimized' => true,
        ];
    }

    /**
     * Generate project overview.
     */
    protected function generateProjectOverview(array $params): array
    {
        return [
            'description' => $params['description'],
            'goals' => $this->generateProjectGoals($params),
            'scope' => $this->generateProjectScope($params),
            'constraints' => $this->generateProjectConstraints($params),
        ];
    }

    /**
     * Generate requirements section.
     */
    protected function generateRequirements(array $params): array
    {
        return [
            'functional_requirements' => $this->generateFunctionalRequirements($params),
            'non_functional_requirements' => $this->generateNonFunctionalRequirements($params),
            'technical_requirements' => $this->generateTechnicalRequirements($params),
            'user_stories' => $this->generateUserStories($params),
        ];
    }

    /**
     * Generate system architecture.
     */
    protected function generateArchitecture(array $params): array
    {
        $architecture = [
            'pattern' => $this->determineArchitecturePattern($params['project_type']),
            'components' => $this->generateArchitectureComponents($params),
            'data_flow' => $this->generateDataFlow($params),
            'security_considerations' => $this->generateSecurityConsiderations($params),
        ];

        if ($params['project_type'] === 'microservice') {
            $architecture['microservices'] = $this->generateMicroservicesArchitecture($params);
        }

        return $architecture;
    }

    /**
     * Generate development phases.
     */
    protected function generatePhases(array $params): array
    {
        $phases = [
            [
                'phase' => 1,
                'name' => 'Project Setup & Foundation',
                'description' => 'Set up development environment, project structure, and core infrastructure',
                'duration' => $this->calculatePhaseDuration(1, $params),
                'dependencies' => [],
                'deliverables' => $this->getPhaseDeliverables(1, $params),
            ],
            [
                'phase' => 2,
                'name' => 'Core Backend Development',
                'description' => 'Implement core business logic, database design, and API endpoints',
                'duration' => $this->calculatePhaseDuration(2, $params),
                'dependencies' => [1],
                'deliverables' => $this->getPhaseDeliverables(2, $params),
            ],
            [
                'phase' => 3,
                'name' => 'Frontend Development',
                'description' => 'Build user interface, user experience, and frontend integrations',
                'duration' => $this->calculatePhaseDuration(3, $params),
                'dependencies' => [2],
                'deliverables' => $this->getPhaseDeliverables(3, $params),
            ],
            [
                'phase' => 4,
                'name' => 'Feature Enhancement',
                'description' => 'Implement advanced features and integrations',
                'duration' => $this->calculatePhaseDuration(4, $params),
                'dependencies' => [2, 3],
                'deliverables' => $this->getPhaseDeliverables(4, $params),
            ],
        ];

        if ($params['include_testing']) {
            $phases[] = [
                'phase' => 5,
                'name' => 'Testing & Quality Assurance',
                'description' => 'Comprehensive testing, bug fixes, and performance optimization',
                'duration' => $this->calculatePhaseDuration(5, $params),
                'dependencies' => [1, 2, 3, 4],
                'deliverables' => $this->getPhaseDeliverables(5, $params),
            ];
        }

        if ($params['include_deployment']) {
            $phases[] = [
                'phase' => count($phases) + 1,
                'name' => 'Deployment & Launch',
                'description' => 'Production deployment, monitoring setup, and go-live activities',
                'duration' => $this->calculatePhaseDuration(6, $params),
                'dependencies' => array_keys($phases),
                'deliverables' => $this->getPhaseDeliverables(6, $params),
            ];
        }

        return $phases;
    }

    /**
     * Generate detailed tasks for AI execution.
     */
    protected function generateDetailedTasks(array $params): array
    {
        $tasks = [];
        $taskId = 1;

        // Phase 1: Project Setup
        $tasks = array_merge($tasks, $this->generatePhase1Tasks($taskId, $params));
        $taskId += count(end($tasks));

        // Phase 2: Backend Development
        $tasks = array_merge($tasks, $this->generatePhase2Tasks($taskId, $params));
        $taskId += count(end($tasks));

        // Phase 3: Frontend Development
        $tasks = array_merge($tasks, $this->generatePhase3Tasks($taskId, $params));
        $taskId += count(end($tasks));

        // Phase 4: Advanced Features
        $tasks = array_merge($tasks, $this->generatePhase4Tasks($taskId, $params));

        return $tasks;
    }

    /**
     * Generate Phase 1 tasks (Project Setup).
     */
    protected function generatePhase1Tasks(int &$startId, array $params): array
    {
        return [
            'phase_1_tasks' => [
                [
                    'id' => $startId++,
                    'phase' => 1,
                    'title' => 'Initialize Laravel Project',
                    'description' => 'Create new Laravel project with proper configuration',
                    'acceptance_criteria' => [
                        'Laravel project created and configured',
                        'Environment files set up',
                        'Database connection established',
                        'Basic routing working',
                    ],
                    'ai_instructions' => 'Run: composer create-project laravel/laravel ' . str_replace(' ', '-', strtolower($params['project_name'])),
                    'estimated_hours' => 2,
                    'priority' => 'high',
                    'dependencies' => [],
                ],
                [
                    'id' => $startId++,
                    'phase' => 1,
                    'title' => 'Setup Development Environment',
                    'description' => 'Configure development tools, linting, and code standards',
                    'acceptance_criteria' => [
                        'Code linting configured (Pint/PHP-CS-Fixer)',
                        'PHPStan/Psalm for static analysis',
                        'Git hooks for code quality',
                        'IDE configuration files created',
                    ],
                    'ai_instructions' => 'Install and configure: laravel/pint, phpstan/phpstan, set up pre-commit hooks',
                    'estimated_hours' => 4,
                    'priority' => 'high',
                    'dependencies' => [$startId - 2],
                ],
                [
                    'id' => $startId++,
                    'phase' => 1,
                    'title' => 'Database Design',
                    'description' => 'Design and create database schema with migrations',
                    'acceptance_criteria' => [
                        'Entity Relationship Diagram created',
                        'Migration files created for all entities',
                        'Seeders created for test data',
                        'Database relationships properly defined',
                    ],
                    'ai_instructions' => 'Create migrations using: php artisan make:migration, define relationships, create seeders',
                    'estimated_hours' => 8,
                    'priority' => 'high',
                    'dependencies' => [$startId - 2],
                ],
            ]
        ];
    }

    /**
     * Generate Phase 2 tasks (Backend Development).
     */
    protected function generatePhase2Tasks(int &$startId, array $params): array
    {
        $tasks = [];
        
        foreach ($params['features'] as $feature) {
            $tasks[] = [
                'id' => $startId++,
                'phase' => 2,
                'title' => "Implement {$feature} Backend",
                'description' => "Create backend logic for {$feature} functionality",
                'acceptance_criteria' => [
                    "Model created for {$feature}",
                    "Controller with CRUD operations",
                    "API endpoints defined and tested",
                    "Validation rules implemented",
                    "Database queries optimized",
                ],
                'ai_instructions' => "Create: Model, Controller, Request classes, API routes for {$feature}",
                'estimated_hours' => $this->estimateFeatureHours($feature, 'backend'),
                'priority' => $this->determineFeaturePriority($feature),
                'dependencies' => [1, 3], // Project setup and database design
            ];
        }

        return ['phase_2_tasks' => $tasks];
    }

    /**
     * Generate Phase 3 tasks (Frontend Development).
     */
    protected function generatePhase3Tasks(int &$startId, array $params): array
    {
        $tasks = [];
        
        foreach ($params['features'] as $feature) {
            $tasks[] = [
                'id' => $startId++,
                'phase' => 3,
                'title' => "Implement {$feature} Frontend",
                'description' => "Create user interface for {$feature} functionality",
                'acceptance_criteria' => [
                    "Vue/React components created for {$feature}",
                    "Forms and validation implemented",
                    "API integration completed",
                    "Responsive design implemented",
                    "User experience optimized",
                ],
                'ai_instructions' => "Create: Vue/React components, forms, API calls, styling for {$feature}",
                'estimated_hours' => $this->estimateFeatureHours($feature, 'frontend'),
                'priority' => $this->determineFeaturePriority($feature),
                'dependencies' => [$startId - count($params['features']) - 1], // Corresponding backend task
            ];
        }

        return ['phase_3_tasks' => $tasks];
    }

    /**
     * Generate Phase 4 tasks (Advanced Features).
     */
    protected function generatePhase4Tasks(int &$startId, array $params): array
    {
        $advancedTasks = [
            [
                'id' => $startId++,
                'phase' => 4,
                'title' => 'Implement User Authentication & Authorization',
                'description' => 'Complete user management system with roles and permissions',
                'acceptance_criteria' => [
                    'User registration and login',
                    'Password reset functionality',
                    'Role-based access control',
                    'API authentication (Sanctum/Passport)',
                    'Email verification',
                ],
                'ai_instructions' => 'Implement: Laravel Sanctum/Passport, Spatie permissions, email verification',
                'estimated_hours' => 12,
                'priority' => 'high',
                'dependencies' => [],
            ],
            [
                'id' => $startId++,
                'phase' => 4,
                'title' => 'Add Search Functionality',
                'description' => 'Implement comprehensive search across the application',
                'acceptance_criteria' => [
                    'Full-text search implemented',
                    'Search filters and sorting',
                    'Search performance optimized',
                    'Auto-complete functionality',
                ],
                'ai_instructions' => 'Implement: Laravel Scout, Elasticsearch/Algolia integration, search API endpoints',
                'estimated_hours' => 10,
                'priority' => 'medium',
                'dependencies' => [],
            ],
            [
                'id' => $startId++,
                'phase' => 4,
                'title' => 'Notification System',
                'description' => 'Implement comprehensive notification system',
                'acceptance_criteria' => [
                    'Email notifications',
                    'In-app notifications',
                    'Push notifications (optional)',
                    'Notification preferences',
                ],
                'ai_instructions' => 'Create: Notification classes, email templates, notification center UI',
                'estimated_hours' => 8,
                'priority' => 'medium',
                'dependencies' => [],
            ],
        ];

        return ['phase_4_tasks' => $advancedTasks];
    }

    /**
     * Generate markdown content for the development plan.
     */
    protected function generateMarkdown(array $plan): string
    {
        $markdown = "# {$plan['metadata']['project_name']} - Development Plan\n\n";
        $markdown .= "> **AI-Optimized Development Plan**  \n";
        $markdown .= "> Generated: {$plan['metadata']['created_at']}  \n";
        $markdown .= "> Complexity: {$plan['metadata']['complexity']}  \n";
        $markdown .= "> Version: {$plan['metadata']['version']}\n\n";

        // Table of Contents
        $markdown .= "## 📋 Table of Contents\n\n";
        $markdown .= "- [Project Overview](#project-overview)\n";
        $markdown .= "- [Requirements](#requirements)\n";
        $markdown .= "- [Architecture](#architecture)\n";
        $markdown .= "- [Technology Stack](#technology-stack)\n";
        $markdown .= "- [Development Phases](#development-phases)\n";
        $markdown .= "- [Detailed Tasks](#detailed-tasks)\n";
        if ($plan['testing_strategy']) {
            $markdown .= "- [Testing Strategy](#testing-strategy)\n";
        }
        if ($plan['deployment_strategy']) {
            $markdown .= "- [Deployment Strategy](#deployment-strategy)\n";
        }
        $markdown .= "- [Timeline](#timeline)\n";
        $markdown .= "- [Team Structure](#team-structure)\n";
        $markdown .= "- [Risk Analysis](#risk-analysis)\n";
        $markdown .= "- [Success Metrics](#success-metrics)\n\n";

        // Project Overview
        $markdown .= "## 🎯 Project Overview\n\n";
        $markdown .= "**Description:** {$plan['project_overview']['description']}\n\n";
        $markdown .= "### Goals\n";
        foreach ($plan['project_overview']['goals'] as $goal) {
            $markdown .= "- {$goal}\n";
        }
        $markdown .= "\n";

        // Requirements
        $markdown .= "## 📋 Requirements\n\n";
        $markdown .= "### Functional Requirements\n";
        foreach ($plan['requirements']['functional_requirements'] as $req) {
            $markdown .= "- {$req}\n";
        }
        $markdown .= "\n### User Stories\n";
        foreach ($plan['requirements']['user_stories'] as $story) {
            $markdown .= "- **{$story['role']}**: {$story['story']}\n";
        }
        $markdown .= "\n";

        // Architecture
        $markdown .= "## 🏗️ Architecture\n\n";
        $markdown .= "**Pattern:** {$plan['architecture']['pattern']}\n\n";
        $markdown .= "### Components\n";
        foreach ($plan['architecture']['components'] as $component) {
            $markdown .= "- **{$component['name']}**: {$component['description']}\n";
        }
        $markdown .= "\n";

        // Technology Stack
        $markdown .= "## 💻 Technology Stack\n\n";
        foreach ($plan['tech_stack'] as $category => $technologies) {
            $markdown .= "### " . ucfirst(str_replace('_', ' ', $category)) . "\n";
            foreach ($technologies as $tech) {
                $markdown .= "- {$tech}\n";
            }
            $markdown .= "\n";
        }

        // Development Phases
        $markdown .= "## 📅 Development Phases\n\n";
        foreach ($plan['phases'] as $phase) {
            $markdown .= "### Phase {$phase['phase']}: {$phase['name']}\n";
            $markdown .= "**Duration:** {$phase['duration']} weeks  \n";
            $markdown .= "**Description:** {$phase['description']}\n\n";
            $markdown .= "**Deliverables:**\n";
            foreach ($phase['deliverables'] as $deliverable) {
                $markdown .= "- {$deliverable}\n";
            }
            $markdown .= "\n";
        }

        // Detailed Tasks
        $markdown .= "## ✅ Detailed Tasks\n\n";
        $markdown .= "> **For AI Developers:** Each task includes specific acceptance criteria and implementation instructions.\n\n";
        
        foreach ($plan['tasks'] as $phaseKey => $phaseTasks) {
            $phaseNumber = str_replace('phase_', '', str_replace('_tasks', '', $phaseKey));
            $markdown .= "### Phase {$phaseNumber} Tasks\n\n";
            
            foreach ($phaseTasks as $task) {
                $markdown .= "#### Task #{$task['id']}: {$task['title']}\n";
                $markdown .= "**Priority:** {$task['priority']} | **Estimated Hours:** {$task['estimated_hours']}\n\n";
                $markdown .= "**Description:** {$task['description']}\n\n";
                $markdown .= "**AI Instructions:**\n";
                $markdown .= "```bash\n{$task['ai_instructions']}\n```\n\n";
                $markdown .= "**Acceptance Criteria:**\n";
                foreach ($task['acceptance_criteria'] as $criteria) {
                    $markdown .= "- [ ] {$criteria}\n";
                }
                $markdown .= "\n**Dependencies:** ";
                if (empty($task['dependencies'])) {
                    $markdown .= "None\n\n";
                } else {
                    $markdown .= "Tasks " . implode(', ', $task['dependencies']) . "\n\n";
                }
                $markdown .= "---\n\n";
            }
        }

        // Testing Strategy
        if ($plan['testing_strategy']) {
            $markdown .= "## 🧪 Testing Strategy\n\n";
            foreach ($plan['testing_strategy'] as $testType => $details) {
                $markdown .= "### " . ucfirst(str_replace('_', ' ', $testType)) . "\n";
                if (is_array($details)) {
                    foreach ($details as $detail) {
                        $markdown .= "- {$detail}\n";
                    }
                } else {
                    $markdown .= "{$details}\n";
                }
                $markdown .= "\n";
            }
        }

        // Deployment Strategy
        if ($plan['deployment_strategy']) {
            $markdown .= "## 🚀 Deployment Strategy\n\n";
            foreach ($plan['deployment_strategy'] as $key => $value) {
                $markdown .= "### " . ucfirst(str_replace('_', ' ', $key)) . "\n";
                if (is_array($value)) {
                    foreach ($value as $item) {
                        $markdown .= "- {$item}\n";
                    }
                } else {
                    $markdown .= "{$value}\n";
                }
                $markdown .= "\n";
            }
        }

        // Timeline
        $markdown .= "## ⏱️ Timeline\n\n";
        $markdown .= "**Total Duration:** {$plan['project_timeline']['total_duration']} weeks\n\n";
        foreach ($plan['project_timeline']['milestones'] as $milestone) {
            $markdown .= "- **Week {$milestone['week']}:** {$milestone['milestone']}\n";
        }
        $markdown .= "\n";

        // Success Metrics
        $markdown .= "## 📊 Success Metrics\n\n";
        foreach ($plan['success_metrics'] as $metric) {
            $markdown .= "- {$metric}\n";
        }
        $markdown .= "\n";

        $markdown .= "---\n\n";
        $markdown .= "*This development plan was generated by Laravel App Developer MCP Server and optimized for AI-assisted development.*\n";

        return $markdown;
    }

    /**
     * Save the plan to a markdown file.
     */
    protected function savePlanToFile(string $content, string $filename): string
    {
        $outputDir = config('laravel-app-developer.mcp.development_plans.output_directory', base_path('development-plans'));
        
        if (!File::exists($outputDir)) {
            File::makeDirectory($outputDir, 0755, true);
        }

        $filePath = $outputDir . DIRECTORY_SEPARATOR . $filename;
        File::put($filePath, $content);

        return $filePath;
    }

    // Helper methods for generating specific sections

    protected function generateProjectGoals(array $params): array
    {
        return [
            "Create a fully functional {$params['project_type']} for {$params['target_audience']}",
            "Implement all requested features with high quality",
            "Ensure scalable and maintainable code architecture",
            "Deliver within the specified timeline and budget",
        ];
    }

    protected function generateProjectScope(array $params): array
    {
        return [
            "Development of {$params['project_name']}",
            "Implementation of " . count($params['features']) . " core features",
            "Responsive web interface (if applicable)",
            "API development for data operations",
            "Database design and implementation",
        ];
    }

    protected function generateProjectConstraints(array $params): array
    {
        return [
            "Timeline: {$params['timeline']}",
            "Technology stack: {$params['tech_stack']}",
            "Target audience: {$params['target_audience']}",
            "Complexity level: {$params['complexity']}",
        ];
    }

    protected function generateFunctionalRequirements(array $params): array
    {
        $requirements = [];
        foreach ($params['features'] as $feature) {
            $requirements[] = "The system shall support {$feature} functionality";
        }
        return $requirements;
    }

    protected function generateNonFunctionalRequirements(array $params): array
    {
        return [
            'Performance: Response time < 2 seconds',
            'Scalability: Support for 1000+ concurrent users',
            'Security: Data encryption and secure authentication',
            'Usability: Intuitive user interface',
            'Reliability: 99.9% uptime',
        ];
    }

    protected function generateTechnicalRequirements(array $params): array
    {
        return [
            "Backend: {$params['tech_stack']}",
            'Database: MySQL/PostgreSQL',
            'Frontend: Modern JavaScript framework',
            'API: RESTful API design',
            'Security: OAuth 2.0 / JWT authentication',
        ];
    }

    protected function generateUserStories(array $params): array
    {
        $stories = [];
        foreach ($params['features'] as $feature) {
            $stories[] = [
                'role' => 'As a user',
                'story' => "I want to {$feature} so that I can accomplish my goals effectively",
            ];
        }
        return $stories;
    }

    protected function determineArchitecturePattern(string $projectType): string
    {
        $patterns = [
            'web_application' => 'MVC (Model-View-Controller)',
            'mobile_app' => 'MVVM (Model-View-ViewModel)',
            'api' => 'RESTful API with Repository Pattern',
            'microservice' => 'Microservices Architecture',
            'full_stack' => 'Layered Architecture',
        ];

        return $patterns[$projectType] ?? 'MVC (Model-View-Controller)';
    }

    protected function generateArchitectureComponents(array $params): array
    {
        return [
            ['name' => 'Presentation Layer', 'description' => 'User interface and user experience components'],
            ['name' => 'Business Logic Layer', 'description' => 'Core application logic and business rules'],
            ['name' => 'Data Access Layer', 'description' => 'Database operations and data management'],
            ['name' => 'API Layer', 'description' => 'RESTful API endpoints for data operations'],
            ['name' => 'Authentication Layer', 'description' => 'User authentication and authorization'],
        ];
    }

    protected function generateDataFlow(array $params): array
    {
        return [
            'Client requests → API Layer',
            'API Layer → Authentication Layer',
            'API Layer → Business Logic Layer',
            'Business Logic Layer → Data Access Layer',
            'Data Access Layer → Database',
        ];
    }

    protected function generateSecurityConsiderations(array $params): array
    {
        return [
            'Input validation and sanitization',
            'SQL injection prevention',
            'XSS protection',
            'CSRF token implementation',
            'Rate limiting for API endpoints',
            'Secure password hashing',
            'JWT token management',
        ];
    }

    protected function generateTechStackDetails(string $techStack): array
    {
        $stacks = explode(',', $techStack);
        $details = [
            'backend' => [],
            'frontend' => [],
            'database' => [],
            'tools' => [],
        ];

        foreach ($stacks as $tech) {
            $tech = trim($tech);
            $lower = strtolower($tech);
            
            if (str_contains($lower, 'laravel') || str_contains($lower, 'php')) {
                $details['backend'][] = $tech;
            } elseif (str_contains($lower, 'vue') || str_contains($lower, 'react') || str_contains($lower, 'angular')) {
                $details['frontend'][] = $tech;
            } elseif (str_contains($lower, 'mysql') || str_contains($lower, 'postgres') || str_contains($lower, 'sqlite')) {
                $details['database'][] = $tech;
            } else {
                $details['tools'][] = $tech;
            }
        }

        return $details;
    }

    protected function calculatePhaseDuration(int $phase, array $params): int
    {
        $baseDurations = [1 => 2, 2 => 4, 3 => 3, 4 => 3, 5 => 2, 6 => 1];
        $complexity = $params['complexity'];
        
        $multiplier = match($complexity) {
            'simple' => 0.7,
            'medium' => 1.0,
            'complex' => 1.5,
            'enterprise' => 2.0,
            default => 1.0,
        };

        return (int) ceil($baseDurations[$phase] * $multiplier);
    }

    protected function getPhaseDeliverables(int $phase, array $params): array
    {
        $deliverables = [
            1 => ['Project repository setup', 'Development environment configured', 'Database schema designed'],
            2 => ['Core models and controllers', 'API endpoints', 'Database migrations'],
            3 => ['User interface components', 'Frontend-backend integration', 'Responsive design'],
            4 => ['Advanced features implemented', 'Third-party integrations', 'Performance optimizations'],
            5 => ['Test suites completed', 'Bug fixes and optimizations', 'Code review completed'],
            6 => ['Production deployment', 'Monitoring setup', 'Documentation completed'],
        ];

        return $deliverables[$phase] ?? [];
    }

    protected function estimateFeatureHours(string $feature, string $type): int
    {
        $baseHours = ['backend' => 6, 'frontend' => 4];
        
        $complexFeatures = ['reporting', 'analytics', 'integration', 'automation'];
        $isComplex = false;
        
        foreach ($complexFeatures as $complex) {
            if (str_contains(strtolower($feature), $complex)) {
                $isComplex = true;
                break;
            }
        }

        $hours = $baseHours[$type];
        return $isComplex ? $hours * 2 : $hours;
    }

    protected function determineFeaturePriority(string $feature): string
    {
        $highPriority = ['authentication', 'user', 'login', 'security'];
        $lowPriority = ['reporting', 'analytics', 'export'];
        
        $feature = strtolower($feature);
        
        foreach ($highPriority as $high) {
            if (str_contains($feature, $high)) {
                return 'high';
            }
        }
        
        foreach ($lowPriority as $low) {
            if (str_contains($feature, $low)) {
                return 'low';
            }
        }
        
        return 'medium';
    }

    protected function generateTestingStrategy(array $params): array
    {
        return [
            'unit_testing' => [
                'PHPUnit for backend testing',
                'Test coverage minimum 80%',
                'Model and service layer tests',
            ],
            'integration_testing' => [
                'API endpoint testing',
                'Database integration tests',
                'Third-party service mocking',
            ],
            'frontend_testing' => [
                'Vue Test Utils / React Testing Library',
                'Component unit tests',
                'End-to-end testing with Cypress',
            ],
            'performance_testing' => [
                'Load testing with Apache Bench',
                'Database query optimization',
                'Frontend performance audits',
            ],
        ];
    }

    protected function generateDeploymentStrategy(array $params): array
    {
        return [
            'staging_environment' => [
                'Staging server setup',
                'Automated deployment pipeline',
                'Environment configuration management',
            ],
            'production_deployment' => [
                'Production server configuration',
                'SSL certificate setup',
                'Database migration strategy',
                'Zero-downtime deployment',
            ],
            'monitoring' => [
                'Application performance monitoring',
                'Error tracking and logging',
                'Uptime monitoring',
                'Security monitoring',
            ],
        ];
    }

    protected function generateTimeline(array $params): array
    {
        $totalWeeks = array_sum(array_column($this->generatePhases($params), 'duration'));
        
        $milestones = [];
        $currentWeek = 0;
        
        foreach ($this->generatePhases($params) as $phase) {
            $currentWeek += $phase['duration'];
            $milestones[] = [
                'week' => $currentWeek,
                'milestone' => "{$phase['name']} completed",
            ];
        }

        return [
            'total_duration' => $totalWeeks,
            'milestones' => $milestones,
        ];
    }

    protected function generateTeamStructure(array $params): array
    {
        $complexity = $params['complexity'];
        
        return match($complexity) {
            'simple' => ['Full-stack Developer', 'Project Manager'],
            'medium' => ['Backend Developer', 'Frontend Developer', 'Project Manager'],
            'complex' => ['Backend Developer', 'Frontend Developer', 'DevOps Engineer', 'QA Engineer', 'Project Manager'],
            'enterprise' => ['Senior Backend Developer', 'Senior Frontend Developer', 'DevOps Engineer', 'QA Engineer', 'Security Specialist', 'Project Manager', 'Technical Lead'],
            default => ['Backend Developer', 'Frontend Developer', 'Project Manager'],
        };
    }

    protected function generateRiskAnalysis(array $params): array
    {
        return [
            'technical_risks' => [
                'Technology learning curve',
                'Third-party API dependencies',
                'Performance bottlenecks',
                'Security vulnerabilities',
            ],
            'project_risks' => [
                'Scope creep',
                'Timeline delays',
                'Resource availability',
                'Requirement changes',
            ],
            'mitigation_strategies' => [
                'Regular code reviews',
                'Continuous integration/deployment',
                'Comprehensive testing',
                'Documentation and knowledge sharing',
            ],
        ];
    }

    protected function generateSuccessMetrics(array $params): array
    {
        return [
            'Technical Metrics: Code coverage > 80%, Performance < 2s response time',
            'Project Metrics: Delivered on time and within budget',
            'Quality Metrics: Bug-free production deployment',
            'User Metrics: Positive user feedback and adoption',
            'Business Metrics: All requirements met and validated',
        ];
    }

    protected function countTotalTasks(array $plan): int
    {
        $count = 0;
        foreach ($plan['tasks'] as $phaseTasks) {
            $count += count($phaseTasks);
        }
        return $count;
    }

    protected function getPlanStructure(array $plan): array
    {
        return [
            'phases' => array_column($plan['phases'], 'name'),
            'task_distribution' => array_map(fn($tasks) => count($tasks), $plan['tasks']),
            'includes_testing' => !is_null($plan['testing_strategy']),
            'includes_deployment' => !is_null($plan['deployment_strategy']),
        ];
    }

    protected function generateAIInstructions(array $plan): array
    {
        return [
            'overview' => 'This development plan is optimized for AI-assisted development',
            'task_format' => 'Each task includes specific acceptance criteria and implementation commands',
            'dependencies' => 'Task dependencies are clearly marked - complete prerequisite tasks first',
            'testing' => 'Run tests after each major feature implementation',
            'git_workflow' => 'Commit frequently with descriptive messages, create feature branches',
            'code_standards' => 'Follow PSR standards for PHP, use consistent naming conventions',
        ];
    }
}