# Laravel App Developer MCP Server

> **The Ultimate MCP Server for Laravel Application Research, Analysis, and Development Planning**

[![Latest Version](https://img.shields.io/github/v/release/STAFE-GROUP-AB/Laravel-App-Developer)](https://github.com/STAFE-GROUP-AB/Laravel-App-Developer/releases)
[![License](https://img.shields.io/github/license/STAFE-GROUP-AB/Laravel-App-Developer)](LICENSE)
[![Issues](https://img.shields.io/github/issues/STAFE-GROUP-AB/Laravel-App-Developer)](https://github.com/STAFE-GROUP-AB/Laravel-App-Developer/issues)

Laravel App Developer is a powerful Model Context Protocol (MCP) server designed specifically for Laravel developers and AI assistants. It provides world-class application analysis, market research, competitive analysis, and development planning capabilities - making it the most comprehensive tool for designing and building successful Laravel applications.

## 🚀 Key Features

### 🔍 **Application Analysis**
- **Comprehensive Laravel App Scanning**: Analyzes your entire Laravel application structure including models, controllers, routes, views, middleware, jobs, events, and more
- **Feature Inventory**: Creates detailed feature lists and component analysis
- **Code Quality Assessment**: Evaluates architecture patterns and identifies improvement opportunities
- **Technology Stack Analysis**: Identifies all packages, dependencies, and technologies in use

### 🏆 **Market Research & Competitive Analysis**
- **Market Leader Research**: Automatically researches top 10 competitors in any application category (CRM, e-commerce, project management, etc.)
- **Feature Comparison**: Compares your application features with market leaders to identify gaps
- **Competitive Positioning**: Analyzes your competitive advantages and weaknesses
- **Market Trends**: Identifies emerging trends and technologies in your industry

### 📋 **AI-Optimized Development Planning**
- **Comprehensive Development Plans**: Generates detailed `DEVELOPMENT_PLAN.md` files optimized for AI assistants like Claude Code
- **Structured Task Breakdown**: Creates specific, actionable tasks with acceptance criteria and implementation instructions
- **Timeline Estimation**: Provides realistic development timelines and effort estimates
- **Phase-based Development**: Organizes development into logical phases with dependencies

### 🎯 **System Design & Architecture**
- **Complete System Design**: Takes high-level requirements (e.g., "design the best CRM in the world") and creates comprehensive system specifications
- **Architecture Planning**: Defines system architecture, technology stack, and scalability considerations
- **Feature Specification**: Detailed feature sets based on complexity level and target audience
- **Business Case Development**: ROI analysis and business justification

### 💡 **Intelligent Feature Suggestions**
- **Gap Analysis**: Identifies missing features based on market analysis
- **Priority Scoring**: Prioritizes features based on market adoption, impact, and effort
- **Innovation Opportunities**: Suggests cutting-edge features for competitive advantage
- **Implementation Roadmap**: Provides actionable implementation plans

## 🛠️ Installation

### Requirements
- PHP 8.1 or higher
- Laravel 10.0 or higher
- Composer

### Install via Composer

```bash
composer require stafe-group-ab/laravel-app-developer --dev
```

### Install the MCP Server

```bash
php artisan laravel-app-developer:install
```

This will:
- Publish the configuration file
- Create the development plans directory
- Display setup instructions for your AI assistant

## ⚙️ Configuration

### MCP Server Registration

Add the Laravel App Developer MCP Server to your AI assistant configuration:

**Claude Desktop (`claude_desktop_config.json`):**
```json
{
    "mcpServers": {
        "laravel-app-developer": {
            "command": "php",
            "args": ["./artisan", "laravel-app-developer:mcp"]
        }
    }
}
```

**Cursor IDE:**
```json
{
    "mcp": {
        "servers": {
            "laravel-app-developer": {
                "command": "php",
                "args": ["./artisan", "laravel-app-developer:mcp"]
            }
        }
    }
}
```

### Configuration File

Customize the MCP server behavior by editing `config/laravel-app-developer.php`:

```php
return [
    'mcp' => [
        'tools' => [
            'exclude' => [], // Tools to exclude
            'include' => [], // Additional tools to include
        ],
        'market_research' => [
            'enabled' => true,
            'cache_ttl' => 3600,
            'max_competitors' => 10,
        ],
        'development_plans' => [
            'output_directory' => base_path('development-plans'),
            'template_style' => 'detailed',
            'include_estimates' => true,
        ],
        'analysis' => [
            'scan_directories' => ['app', 'resources/views', 'routes'],
            'extract_features' => [
                'models' => true,
                'controllers' => true,
                'routes' => true,
                // ... more options
            ],
        ],
    ],
];
```

## 🔧 Available Tools

### 1. `analyze-application`
Comprehensively analyzes your Laravel application structure and features.

**Example Usage:**
```
Analyze my Laravel application and provide a detailed feature inventory.
```

**Parameters:**
- `include_code_samples` (optional): Include code samples in analysis
- `deep_analysis` (optional): Perform deep analysis with method signatures
- `focus_area` (optional): Focus on specific area (models, controllers, routes, etc.)

### 2. `research-market-leaders`
Researches top competitors and market leaders in your application category.

**Example Usage:**
```
Research the top 10 CRM applications and analyze their features.
```

**Parameters:**
- `category` (required): Application category (crm, e-commerce, project-management, etc.)
- `limit` (optional): Number of competitors to research (max 20)
- `focus_area` (optional): Focus on features, pricing, technology, or user_experience
- `include_startups` (optional): Include emerging companies
- `market_segment` (optional): Target enterprise, sme, consumer, or all

### 3. `compare-features`
Compares your application features with market leaders to identify gaps.

**Example Usage:**
```
Compare my CRM features with the top market leaders and identify what I'm missing.
```

**Parameters:**
- `category` (required): Application category for comparison
- `current_features` (optional): Your current features list
- `market_leaders_data` (optional): Market research data
- `comparison_focus` (optional): Focus area for comparison
- `priority_threshold` (optional): Minimum adoption percentage for high priority

### 4. `generate-development-plan`
Creates comprehensive, AI-optimized development plans in markdown format.

**Example Usage:**
```
Generate a development plan for building a modern e-commerce platform with Laravel.
```

**Parameters:**
- `project_name` (required): Name of the project
- `project_type` (required): Type of project (web_application, mobile_app, api, etc.)
- `description` (required): Detailed project description
- `features` (optional): List of features to include
- `tech_stack` (optional): Preferred technology stack
- `timeline` (optional): Desired timeline (sprint, month, quarter)
- `complexity` (optional): Project complexity (simple, medium, complex, enterprise)
- `include_testing` (optional): Include testing strategies
- `include_deployment` (optional): Include deployment tasks

### 5. `design-system`
Designs complete software systems from high-level requirements.

**Example Usage:**
```
Design the best CRM system in the world for enterprise customers.
```

**Parameters:**
- `system_description` (required): High-level system description
- `system_type` (optional): Type of system to design
- `target_users` (optional): Primary target users
- `complexity_level` (optional): System complexity level
- `must_have_features` (optional): Required features
- `business_model` (optional): Business model (saas, marketplace, etc.)
- `include_ai_features` (optional): Include AI capabilities
- `mobile_first` (optional): Mobile-first design approach

### 6. `suggest-features`
Suggests new features based on market analysis and competitive landscape.

**Example Usage:**
```
Suggest new features for my project management application based on market trends.
```

**Parameters:**
- `app_category` (required): Your application category
- `current_features` (optional): List of your current features
- `target_users` (optional): Target user types
- `business_stage` (optional): Business stage (startup, growth, mature, enterprise)
- `suggestion_focus` (optional): Focus area for suggestions
- `include_emerging_trends` (optional): Include emerging technology trends
- `max_suggestions` (optional): Maximum number of suggestions
- `budget_consideration` (optional): Budget constraints
- `development_timeline` (optional): Preferred timeline

## 📖 Usage Examples

### Complete Application Analysis Workflow

```bash
# 1. Analyze your current Laravel application
"Analyze my Laravel application with deep analysis and code samples"

# 2. Research your market category
"Research the top 10 CRM applications, focusing on features and technology"

# 3. Compare features and identify gaps
"Compare my application features with the CRM market leaders and identify critical gaps"

# 4. Generate feature suggestions
"Suggest new features for my CRM application focusing on competitive advantage"

# 5. Create a development plan
"Generate a comprehensive development plan to implement the top 5 suggested features"
```

### System Design from Scratch

```bash
# Design a complete system
"Design the best project management system for software development teams, 
include AI features, mobile-first approach, and enterprise-level complexity"

# Generate implementation roadmap
"Create a detailed development plan for the project management system you just designed"
```

### Market Research & Competitive Analysis

```bash
# Research specific market segment
"Research e-commerce platforms targeting small businesses, include emerging startups"

# Compare with specific focus
"Compare my e-commerce platform with market leaders, focusing on user experience and technology"

# Get innovation suggestions
"Suggest innovative features for my e-commerce platform based on emerging trends"
```

## 📁 Output Files

The MCP server generates several types of output files:

### Development Plans (`development-plans/`)
- `DEVELOPMENT_PLAN.md`: Comprehensive development roadmap
- AI-optimized with specific implementation instructions
- Includes phases, tasks, timelines, and acceptance criteria

### Market Research Reports
- Competitor analysis and feature comparisons
- Market trend insights and opportunities
- Competitive positioning analysis

## 🎯 Best Practices

### For AI Assistants
1. **Start with Analysis**: Always begin by analyzing the current application state
2. **Research Before Building**: Research market leaders before adding new features
3. **Use Development Plans**: Generate development plans for complex implementations
4. **Iterative Approach**: Use the tools iteratively to refine and improve plans

### For Developers
1. **Regular Analysis**: Periodically analyze your application to track evolution
2. **Market Awareness**: Stay updated with market trends and competitor features
3. **Structured Development**: Follow generated development plans for better organization
4. **Feature Prioritization**: Use feature suggestions to prioritize your roadmap

## 🤝 Contributing

We welcome contributions! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details on how to contribute to this project.

### Development Setup

```bash
# Clone the repository
git clone https://github.com/STAFE-GROUP-AB/Laravel-App-Developer.git

# Install dependencies
composer install

# Run tests
composer test

# Run linting
composer lint
```

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- Built on the excellent [Laravel MCP](https://github.com/laravel/mcp) foundation
- Inspired by [Laravel Boost](https://github.com/laravel/boost) architecture
- Thanks to the Laravel community for their continuous innovation

## 🔗 Related Projects

- [Laravel MCP](https://github.com/laravel/mcp) - The underlying MCP server framework
- [Laravel Boost](https://github.com/laravel/boost) - AI-assisted Laravel development
- [Model Context Protocol](https://github.com/modelcontextprotocol) - The MCP specification

---

**Made with ❤️ for the Laravel community**

*Transform your Laravel development workflow with intelligent application analysis, market research, and AI-optimized development planning.*
