import static org.junit.Assert.*;
import org.junit.Test;
import org.junit.rules.Timeout;
import com.gradescope.jh61b.grader.GradedTest;

public class ExampleTests {

	@Rule
	public Timeout globalTimeout = Timeout.seconds(2);
	
	@Test
	@GradedTest (name="Name of Test",max_score=50) // 50 points if passed, 0 points if failed
	public void testOne() {
		assertTrue("This doesn't get printed if the test succeeds.", true);
	}

	@Test
	@GradedTest (name="Name of Test 2",max_score=45) // 45 points if passed, 0 points if failed
	public void testTwo() {
	    assertTrue("This is the error message for students.", false);
    }
    
}
