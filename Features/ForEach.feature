# @author Anthon Pang <anthonp@nationalfibre.net>

@dev @javascript
Feature: ForEach
    In order to facilitate Behat testing
    As a tester
    I want to iterate through some situations (at a sub-scenario level).

    Scenario: simple two step
        Given I will go to "{page}"
        And I should later see "{text}"
        Then do it for each:
            | page                | text              |
            | /authenticate/login | Keep me logged in |

    Scenario: multi-step
        Given I will be on "{page1}"
        And I should later be on "{page2}"
        And I should later see "{text1}"
        Then I should later also see "{text2}" for each:
            | page1               | page2               | text1             | text2          |
            | /authenticate/login | /authenticate/login | Keep me logged in | Register New   |
            | /public/faq         | /public/faq         | Frequently Asked  | Privacy Policy |

# These are expected to fail but @expectedException isn't supported by Behat

#    @expectedException AmbiguousException
#    Scenario: missing table
#        Given I will be on "{page}" for each:

#    @expectedException AmbiguousException
#    Scenario: missing "for each"
#        Given I will be on "{page}"
#            | page |
#            | /    |
